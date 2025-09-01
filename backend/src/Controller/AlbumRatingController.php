<?php

namespace App\Controller;

use App\Entity\AlbumRating;
use App\Repository\AlbumRatingRepository;
use App\Repository\UserRepository;
use App\Repository\AlbumRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/album-ratings')]
class AlbumRatingController extends AbstractController
{
    public function __construct(
        private AlbumRatingRepository $albumRatingRepository,
        private UserRepository $userRepository,
        private AlbumRepository $albumRepository,
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $albumRatings = $this->albumRatingRepository->findAll();
        $responseData = [];
        
        foreach ($albumRatings as $albumRating) {
            $responseData[] = [
                'ratingUser' => [
                    'id' => $albumRating->getRatingUser()?->getId(),
                    'username' => $albumRating->getRatingUser()?->getUsername()
                ],
                'ratedAlbum' => [
                    'id' => $albumRating->getRatedAlbum()?->getId(),
                    'name' => $albumRating->getRatedAlbum()?->getName()
                ],
                'rateType' => $albumRating->getRateType(),
                'ratedAt' => $albumRating->getRatedAt()?->format('Y-m-d H:i:s')
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
        
        $album = $this->albumRepository->find($data['ratedAlbumId'] ?? null);
        if (!$album) {
            return $this->json(['error' => 'Album not found'], Response::HTTP_BAD_REQUEST);
        }
        
        $existingRating = $this->albumRatingRepository->findOneBy([
            'ratingUser' => $user,
            'ratedAlbum' => $album
        ]);
        if ($existingRating) {
            return $this->json(['error' => 'User has already rated this album'], Response::HTTP_BAD_REQUEST);
        }
        
        $albumRating = new AlbumRating();
        $albumRating->setRatingUser($user);
        $albumRating->setRatedAlbum($album);
        $albumRating->setRateType($data['rateType'] ?? '');
        
        if (isset($data['ratedAt'])) {
            $albumRating->setRatedAt(new \DateTime($data['ratedAt']));
        }
        
        $errors = $this->validator->validate($albumRating);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }
        
        try {
            $this->entityManager->persist($albumRating);
            $this->entityManager->flush();
            
            return $this->json([
                'ratingUser' => [
                    'id' => $albumRating->getRatingUser()?->getId(),
                    'username' => $albumRating->getRatingUser()?->getUsername()
                ],
                'ratedAlbum' => [
                    'id' => $albumRating->getRatedAlbum()?->getId(),
                    'name' => $albumRating->getRatedAlbum()?->getName()
                ],
                'rateType' => $albumRating->getRateType(),
                'ratedAt' => $albumRating->getRatedAt()?->format('Y-m-d H:i:s')
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/user/{userId}/album/{albumId}', methods: ['GET'])]
    public function show(int $userId, int $albumId): JsonResponse
    {
        $user = $this->userRepository->find($userId);
        if (!$user) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }
        
        $album = $this->albumRepository->find($albumId);
        if (!$album) {
            return $this->json(['error' => 'Album not found'], Response::HTTP_NOT_FOUND);
        }
        
        $albumRating = $this->albumRatingRepository->findOneBy([
            'ratingUser' => $user,
            'ratedAlbum' => $album
        ]);
        
        if (!$albumRating) {
            return $this->json(['error' => 'Album rating not found'], Response::HTTP_NOT_FOUND);
        }
        
        return $this->json([
            'ratingUser' => [
                'id' => $albumRating->getRatingUser()?->getId(),
                'username' => $albumRating->getRatingUser()?->getUsername()
            ],
            'ratedAlbum' => [
                'id' => $albumRating->getRatedAlbum()?->getId(),
                'name' => $albumRating->getRatedAlbum()?->getName()
            ],
            'rateType' => $albumRating->getRateType(),
            'ratedAt' => $albumRating->getRatedAt()?->format('Y-m-d H:i:s')
        ]);
    }

    #[Route('/user/{userId}/album/{albumId}', methods: ['PUT'])]
    public function update(int $userId, int $albumId, Request $request): JsonResponse
    {
        $user = $this->userRepository->find($userId);
        if (!$user) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }
        
        $album = $this->albumRepository->find($albumId);
        if (!$album) {
            return $this->json(['error' => 'Album not found'], Response::HTTP_NOT_FOUND);
        }
        
        $albumRating = $this->albumRatingRepository->findOneBy([
            'ratingUser' => $user,
            'ratedAlbum' => $album
        ]);
        
        if (!$albumRating) {
            return $this->json(['error' => 'Album rating not found'], Response::HTTP_NOT_FOUND);
        }
        
        $data = json_decode($request->getContent(), true);
        
        if (isset($data['rateType'])) {
            $albumRating->setRateType($data['rateType']);
        }
        if (isset($data['ratedAt'])) {
            $albumRating->setRatedAt(new \DateTime($data['ratedAt']));
        }
        
        $errors = $this->validator->validate($albumRating);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }
        
        try {
            $this->entityManager->flush();
            
            return $this->json([
                'ratingUser' => [
                    'id' => $albumRating->getRatingUser()?->getId(),
                    'username' => $albumRating->getRatingUser()?->getUsername()
                ],
                'ratedAlbum' => [
                    'id' => $albumRating->getRatedAlbum()?->getId(),
                    'name' => $albumRating->getRatedAlbum()?->getName()
                ],
                'rateType' => $albumRating->getRateType(),
                'ratedAt' => $albumRating->getRatedAt()?->format('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/user/{userId}/album/{albumId}', methods: ['DELETE'])]
    public function delete(int $userId, int $albumId): JsonResponse
    {
        $user = $this->userRepository->find($userId);
        if (!$user) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }
        
        $album = $this->albumRepository->find($albumId);
        if (!$album) {
            return $this->json(['error' => 'Album not found'], Response::HTTP_NOT_FOUND);
        }
        
        $albumRating = $this->albumRatingRepository->findOneBy([
            'ratingUser' => $user,
            'ratedAlbum' => $album
        ]);
        
        if (!$albumRating) {
            return $this->json(['error' => 'Album rating not found'], Response::HTTP_NOT_FOUND);
        }
        
        try {
            $this->entityManager->remove($albumRating);
            $this->entityManager->flush();
            return $this->json(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}