<?php

namespace App\Controller;

use App\Entity\TrackPlaylist;
use App\Repository\TrackPlaylistRepository;
use App\Repository\TrackRepository;
use App\Repository\PlaylistRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/track-playlists')]
class TrackPlaylistController extends AbstractController
{
    public function __construct(
        private TrackPlaylistRepository $trackPlaylistRepository,
        private TrackRepository $trackRepository,
        private PlaylistRepository $playlistRepository,
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $trackPlaylists = $this->trackPlaylistRepository->findAll();
        $responseData = [];
        
        foreach ($trackPlaylists as $trackPlaylist) {
            $responseData[] = [
                'track' => [
                    'id' => $trackPlaylist->getTrack()?->getId(),
                    'name' => $trackPlaylist->getTrack()?->getName()
                ],
                'playlist' => [
                    'id' => $trackPlaylist->getPlaylist()?->getId(),
                    'name' => $trackPlaylist->getPlaylist()?->getName()
                ],
                'addedAt' => $trackPlaylist->getAddedAt()?->format('Y-m-d H:i:s')
            ];
        }
        
        return $this->json($responseData);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $track = $this->trackRepository->find($data['trackId'] ?? null);
        if (!$track) {
            return $this->json(['error' => 'Track not found'], Response::HTTP_BAD_REQUEST);
        }
        
        $playlist = $this->playlistRepository->find($data['playlistId'] ?? null);
        if (!$playlist) {
            return $this->json(['error' => 'Playlist not found'], Response::HTTP_BAD_REQUEST);
        }
        
        $existingTrackPlaylist = $this->trackPlaylistRepository->findOneBy([
            'track' => $track,
            'playlist' => $playlist
        ]);
        if ($existingTrackPlaylist) {
            return $this->json(['error' => 'Track-Playlist relationship already exists'], Response::HTTP_BAD_REQUEST);
        }
        
        $trackPlaylist = new TrackPlaylist();
        $trackPlaylist->setTrack($track);
        $trackPlaylist->setPlaylist($playlist);
        
        if (isset($data['addedAt'])) {
            $trackPlaylist->setAddedAt(new \DateTime($data['addedAt']));
        }
        
        $errors = $this->validator->validate($trackPlaylist);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }
        
        try {
            $this->entityManager->persist($trackPlaylist);
            $this->entityManager->flush();
            
            return $this->json([
                'track' => [
                    'id' => $trackPlaylist->getTrack()?->getId(),
                    'name' => $trackPlaylist->getTrack()?->getName()
                ],
                'playlist' => [
                    'id' => $trackPlaylist->getPlaylist()?->getId(),
                    'name' => $trackPlaylist->getPlaylist()?->getName()
                ],
                'addedAt' => $trackPlaylist->getAddedAt()?->format('Y-m-d H:i:s')
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/track/{trackId}/playlist/{playlistId}', methods: ['GET'])]
    public function show(int $trackId, int $playlistId): JsonResponse
    {
        $track = $this->trackRepository->find($trackId);
        if (!$track) {
            return $this->json(['error' => 'Track not found'], Response::HTTP_NOT_FOUND);
        }
        
        $playlist = $this->playlistRepository->find($playlistId);
        if (!$playlist) {
            return $this->json(['error' => 'Playlist not found'], Response::HTTP_NOT_FOUND);
        }
        
        $trackPlaylist = $this->trackPlaylistRepository->findOneBy([
            'track' => $track,
            'playlist' => $playlist
        ]);
        
        if (!$trackPlaylist) {
            return $this->json(['error' => 'Track-Playlist relationship not found'], Response::HTTP_NOT_FOUND);
        }
        
        return $this->json([
            'track' => [
                'id' => $trackPlaylist->getTrack()?->getId(),
                'name' => $trackPlaylist->getTrack()?->getName()
            ],
            'playlist' => [
                'id' => $trackPlaylist->getPlaylist()?->getId(),
                'name' => $trackPlaylist->getPlaylist()?->getName()
            ],
            'addedAt' => $trackPlaylist->getAddedAt()?->format('Y-m-d H:i:s')
        ]);
    }

    #[Route('/track/{trackId}/playlist/{playlistId}', methods: ['PUT'])]
    public function update(int $trackId, int $playlistId, Request $request): JsonResponse
    {
        $track = $this->trackRepository->find($trackId);
        if (!$track) {
            return $this->json(['error' => 'Track not found'], Response::HTTP_NOT_FOUND);
        }
        
        $playlist = $this->playlistRepository->find($playlistId);
        if (!$playlist) {
            return $this->json(['error' => 'Playlist not found'], Response::HTTP_NOT_FOUND);
        }
        
        $trackPlaylist = $this->trackPlaylistRepository->findOneBy([
            'track' => $track,
            'playlist' => $playlist
        ]);
        
        if (!$trackPlaylist) {
            return $this->json(['error' => 'Track-Playlist relationship not found'], Response::HTTP_NOT_FOUND);
        }
        
        $data = json_decode($request->getContent(), true);
        
        if (isset($data['addedAt'])) {
            $trackPlaylist->setAddedAt(new \DateTime($data['addedAt']));
        }
        
        $errors = $this->validator->validate($trackPlaylist);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }
        
        try {
            $this->entityManager->flush();
            
            return $this->json([
                'track' => [
                    'id' => $trackPlaylist->getTrack()?->getId(),
                    'name' => $trackPlaylist->getTrack()?->getName()
                ],
                'playlist' => [
                    'id' => $trackPlaylist->getPlaylist()?->getId(),
                    'name' => $trackPlaylist->getPlaylist()?->getName()
                ],
                'addedAt' => $trackPlaylist->getAddedAt()?->format('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/track/{trackId}/playlist/{playlistId}', methods: ['DELETE'])]
    public function delete(int $trackId, int $playlistId): JsonResponse
    {
        $track = $this->trackRepository->find($trackId);
        if (!$track) {
            return $this->json(['error' => 'Track not found'], Response::HTTP_NOT_FOUND);
        }
        
        $playlist = $this->playlistRepository->find($playlistId);
        if (!$playlist) {
            return $this->json(['error' => 'Playlist not found'], Response::HTTP_NOT_FOUND);
        }
        
        $trackPlaylist = $this->trackPlaylistRepository->findOneBy([
            'track' => $track,
            'playlist' => $playlist
        ]);
        
        if (!$trackPlaylist) {
            return $this->json(['error' => 'Track-Playlist relationship not found'], Response::HTTP_NOT_FOUND);
        }
        
        try {
            $this->entityManager->remove($trackPlaylist);
            $this->entityManager->flush();
            return $this->json(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}