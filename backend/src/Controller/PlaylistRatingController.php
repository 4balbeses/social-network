<?php

namespace App\Controller;

use App\Entity\PlaylistRating;
use App\Repository\PlaylistRatingRepository;
use App\Repository\UserRepository;
use App\Repository\PlaylistRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/playlist-ratings')]
class PlaylistRatingController extends AbstractController
{
    public function __construct(
        private PlaylistRatingRepository $playlistRatingRepository,
        private UserRepository $userRepository,
        private PlaylistRepository $playlistRepository,
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $playlistRatings = $this->playlistRatingRepository->findAll();
        $responseData = [];
        
        foreach ($playlistRatings as $playlistRating) {
            $responseData[] = [
                'ratingUser' => [
                    'id' => $playlistRating->getRatingUser()?->getId(),
                    'username' => $playlistRating->getRatingUser()?->getUsername()
                ],
                'ratedPlaylist' => [
                    'id' => $playlistRating->getRatedPlaylist()?->getId(),
                    'name' => $playlistRating->getRatedPlaylist()?->getName()
                ],
                'rateType' => $playlistRating->getRateType(),
                'ratedAt' => $playlistRating->getRatedAt()?->format('Y-m-d H:i:s')
            ];
        }
        
        return $this->json($responseData);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $user = $this->userRepository->find($data['ratingUserId'] ?? null);
        if (!$user) {
            return $this->json(['error' => 'Rating user not found'], Response::HTTP_BAD_REQUEST);
        }
        
        $playlist = $this->playlistRepository->find($data['ratedPlaylistId'] ?? null);
        if (!$playlist) {
            return $this->json(['error' => 'Playlist not found'], Response::HTTP_BAD_REQUEST);
        }
        
        $existingRating = $this->playlistRatingRepository->findOneBy([
            'ratingUser' => $user,
            'ratedPlaylist' => $playlist
        ]);
        if ($existingRating) {
            return $this->json(['error' => 'User has already rated this playlist'], Response::HTTP_BAD_REQUEST);
        }
        
        $playlistRating = new PlaylistRating();
        $playlistRating->setRatingUser($user);
        $playlistRating->setRatedPlaylist($playlist);
        $playlistRating->setRateType($data['rateType'] ?? '');
        
        if (isset($data['ratedAt'])) {
            $playlistRating->setRatedAt(new \DateTime($data['ratedAt']));
        }
        
        $errors = $this->validator->validate($playlistRating);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }
        
        try {
            $this->entityManager->persist($playlistRating);
            $this->entityManager->flush();
            
            return $this->json([
                'ratingUser' => [
                    'id' => $playlistRating->getRatingUser()?->getId(),
                    'username' => $playlistRating->getRatingUser()?->getUsername()
                ],
                'ratedPlaylist' => [
                    'id' => $playlistRating->getRatedPlaylist()?->getId(),
                    'name' => $playlistRating->getRatedPlaylist()?->getName()
                ],
                'rateType' => $playlistRating->getRateType(),
                'ratedAt' => $playlistRating->getRatedAt()?->format('Y-m-d H:i:s')
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/user/{userId}/playlist/{playlistId}', methods: ['GET'])]
    public function show(int $userId, int $playlistId): JsonResponse
    {
        $user = $this->userRepository->find($userId);
        if (!$user) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }
        
        $playlist = $this->playlistRepository->find($playlistId);
        if (!$playlist) {
            return $this->json(['error' => 'Playlist not found'], Response::HTTP_NOT_FOUND);
        }
        
        $playlistRating = $this->playlistRatingRepository->findOneBy([
            'ratingUser' => $user,
            'ratedPlaylist' => $playlist
        ]);
        
        if (!$playlistRating) {
            return $this->json(['error' => 'Playlist rating not found'], Response::HTTP_NOT_FOUND);
        }
        
        return $this->json([
            'ratingUser' => [
                'id' => $playlistRating->getRatingUser()?->getId(),
                'username' => $playlistRating->getRatingUser()?->getUsername()
            ],
            'ratedPlaylist' => [
                'id' => $playlistRating->getRatedPlaylist()?->getId(),
                'name' => $playlistRating->getRatedPlaylist()?->getName()
            ],
            'rateType' => $playlistRating->getRateType(),
            'ratedAt' => $playlistRating->getRatedAt()?->format('Y-m-d H:i:s')
        ]);
    }

    #[Route('/user/{userId}/playlist/{playlistId}', methods: ['PUT'])]
    public function update(int $userId, int $playlistId, Request $request): JsonResponse
    {
        $user = $this->userRepository->find($userId);
        if (!$user) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }
        
        $playlist = $this->playlistRepository->find($playlistId);
        if (!$playlist) {
            return $this->json(['error' => 'Playlist not found'], Response::HTTP_NOT_FOUND);
        }
        
        $playlistRating = $this->playlistRatingRepository->findOneBy([
            'ratingUser' => $user,
            'ratedPlaylist' => $playlist
        ]);
        
        if (!$playlistRating) {
            return $this->json(['error' => 'Playlist rating not found'], Response::HTTP_NOT_FOUND);
        }
        
        $data = json_decode($request->getContent(), true);
        
        if (isset($data['rateType'])) {
            $playlistRating->setRateType($data['rateType']);
        }
        if (isset($data['ratedAt'])) {
            $playlistRating->setRatedAt(new \DateTime($data['ratedAt']));
        }
        
        $errors = $this->validator->validate($playlistRating);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }
        
        try {
            $this->entityManager->flush();
            
            return $this->json([
                'ratingUser' => [
                    'id' => $playlistRating->getRatingUser()?->getId(),
                    'username' => $playlistRating->getRatingUser()?->getUsername()
                ],
                'ratedPlaylist' => [
                    'id' => $playlistRating->getRatedPlaylist()?->getId(),
                    'name' => $playlistRating->getRatedPlaylist()?->getName()
                ],
                'rateType' => $playlistRating->getRateType(),
                'ratedAt' => $playlistRating->getRatedAt()?->format('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/user/{userId}/playlist/{playlistId}', methods: ['DELETE'])]
    public function delete(int $userId, int $playlistId): JsonResponse
    {
        $user = $this->userRepository->find($userId);
        if (!$user) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }
        
        $playlist = $this->playlistRepository->find($playlistId);
        if (!$playlist) {
            return $this->json(['error' => 'Playlist not found'], Response::HTTP_NOT_FOUND);
        }
        
        $playlistRating = $this->playlistRatingRepository->findOneBy([
            'ratingUser' => $user,
            'ratedPlaylist' => $playlist
        ]);
        
        if (!$playlistRating) {
            return $this->json(['error' => 'Playlist rating not found'], Response::HTTP_NOT_FOUND);
        }
        
        try {
            $this->entityManager->remove($playlistRating);
            $this->entityManager->flush();
            return $this->json(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}