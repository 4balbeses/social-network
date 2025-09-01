<?php

namespace App\Controller;

use App\Entity\TrackAlbum;
use App\Repository\TrackAlbumRepository;
use App\Repository\TrackRepository;
use App\Repository\AlbumRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/track-albums')]
class TrackAlbumController extends AbstractController
{
    public function __construct(
        private TrackAlbumRepository $trackAlbumRepository,
        private TrackRepository $trackRepository,
        private AlbumRepository $albumRepository,
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $trackAlbums = $this->trackAlbumRepository->findAll();
        $responseData = [];
        
        foreach ($trackAlbums as $trackAlbum) {
            $responseData[] = [
                'track' => [
                    'id' => $trackAlbum->getTrack()?->getId(),
                    'name' => $trackAlbum->getTrack()?->getName()
                ],
                'album' => [
                    'id' => $trackAlbum->getAlbum()?->getId(),
                    'name' => $trackAlbum->getAlbum()?->getName()
                ],
                'addedAt' => $trackAlbum->getAddedAt()?->format('Y-m-d H:i:s')
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
        
        $album = $this->albumRepository->find($data['albumId'] ?? null);
        if (!$album) {
            return $this->json(['error' => 'Album not found'], Response::HTTP_BAD_REQUEST);
        }
        
        $existingTrackAlbum = $this->trackAlbumRepository->findOneBy([
            'track' => $track,
            'album' => $album
        ]);
        if ($existingTrackAlbum) {
            return $this->json(['error' => 'Track-Album relationship already exists'], Response::HTTP_BAD_REQUEST);
        }
        
        $trackAlbum = new TrackAlbum();
        $trackAlbum->setTrack($track);
        $trackAlbum->setAlbum($album);
        
        if (isset($data['addedAt'])) {
            $trackAlbum->setAddedAt(new \DateTime($data['addedAt']));
        }
        
        $errors = $this->validator->validate($trackAlbum);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }
        
        try {
            $this->entityManager->persist($trackAlbum);
            $this->entityManager->flush();
            
            return $this->json([
                'track' => [
                    'id' => $trackAlbum->getTrack()?->getId(),
                    'name' => $trackAlbum->getTrack()?->getName()
                ],
                'album' => [
                    'id' => $trackAlbum->getAlbum()?->getId(),
                    'name' => $trackAlbum->getAlbum()?->getName()
                ],
                'addedAt' => $trackAlbum->getAddedAt()?->format('Y-m-d H:i:s')
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/track/{trackId}/album/{albumId}', methods: ['GET'])]
    public function show(int $trackId, int $albumId): JsonResponse
    {
        $track = $this->trackRepository->find($trackId);
        if (!$track) {
            return $this->json(['error' => 'Track not found'], Response::HTTP_NOT_FOUND);
        }
        
        $album = $this->albumRepository->find($albumId);
        if (!$album) {
            return $this->json(['error' => 'Album not found'], Response::HTTP_NOT_FOUND);
        }
        
        $trackAlbum = $this->trackAlbumRepository->findOneBy([
            'track' => $track,
            'album' => $album
        ]);
        
        if (!$trackAlbum) {
            return $this->json(['error' => 'Track-Album relationship not found'], Response::HTTP_NOT_FOUND);
        }
        
        return $this->json([
            'track' => [
                'id' => $trackAlbum->getTrack()?->getId(),
                'name' => $trackAlbum->getTrack()?->getName()
            ],
            'album' => [
                'id' => $trackAlbum->getAlbum()?->getId(),
                'name' => $trackAlbum->getAlbum()?->getName()
            ],
            'addedAt' => $trackAlbum->getAddedAt()?->format('Y-m-d H:i:s')
        ]);
    }

    #[Route('/track/{trackId}/album/{albumId}', methods: ['PUT'])]
    public function update(int $trackId, int $albumId, Request $request): JsonResponse
    {
        $track = $this->trackRepository->find($trackId);
        if (!$track) {
            return $this->json(['error' => 'Track not found'], Response::HTTP_NOT_FOUND);
        }
        
        $album = $this->albumRepository->find($albumId);
        if (!$album) {
            return $this->json(['error' => 'Album not found'], Response::HTTP_NOT_FOUND);
        }
        
        $trackAlbum = $this->trackAlbumRepository->findOneBy([
            'track' => $track,
            'album' => $album
        ]);
        
        if (!$trackAlbum) {
            return $this->json(['error' => 'Track-Album relationship not found'], Response::HTTP_NOT_FOUND);
        }
        
        $data = json_decode($request->getContent(), true);
        
        if (isset($data['addedAt'])) {
            $trackAlbum->setAddedAt(new \DateTime($data['addedAt']));
        }
        
        $errors = $this->validator->validate($trackAlbum);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }
        
        try {
            $this->entityManager->flush();
            
            return $this->json([
                'track' => [
                    'id' => $trackAlbum->getTrack()?->getId(),
                    'name' => $trackAlbum->getTrack()?->getName()
                ],
                'album' => [
                    'id' => $trackAlbum->getAlbum()?->getId(),
                    'name' => $trackAlbum->getAlbum()?->getName()
                ],
                'addedAt' => $trackAlbum->getAddedAt()?->format('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/track/{trackId}/album/{albumId}', methods: ['DELETE'])]
    public function delete(int $trackId, int $albumId): JsonResponse
    {
        $track = $this->trackRepository->find($trackId);
        if (!$track) {
            return $this->json(['error' => 'Track not found'], Response::HTTP_NOT_FOUND);
        }
        
        $album = $this->albumRepository->find($albumId);
        if (!$album) {
            return $this->json(['error' => 'Album not found'], Response::HTTP_NOT_FOUND);
        }
        
        $trackAlbum = $this->trackAlbumRepository->findOneBy([
            'track' => $track,
            'album' => $album
        ]);
        
        if (!$trackAlbum) {
            return $this->json(['error' => 'Track-Album relationship not found'], Response::HTTP_NOT_FOUND);
        }
        
        try {
            $this->entityManager->remove($trackAlbum);
            $this->entityManager->flush();
            return $this->json(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}