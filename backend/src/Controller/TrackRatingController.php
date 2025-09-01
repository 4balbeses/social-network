<?php

namespace App\Controller;

use App\Entity\TrackRating;
use App\Repository\TrackRatingRepository;
use App\Repository\UserRepository;
use App\Repository\TrackRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/track-ratings')]
class TrackRatingController extends AbstractController
{
    public function __construct(
        private TrackRatingRepository $trackRatingRepository,
        private UserRepository $userRepository,
        private TrackRepository $trackRepository,
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $trackRatings = $this->trackRatingRepository->findAll();
        $responseData = [];
        
        foreach ($trackRatings as $trackRating) {
            $responseData[] = [
                'ratingUser' => [
                    'id' => $trackRating->getRatingUser()?->getId(),
                    'username' => $trackRating->getRatingUser()?->getUsername()
                ],
                'ratedTrack' => [
                    'id' => $trackRating->getRatedTrack()?->getId(),
                    'name' => $trackRating->getRatedTrack()?->getName()
                ],
                'rateType' => $trackRating->getRateType(),
                'ratedAt' => $trackRating->getRatedAt()?->format('Y-m-d H:i:s')
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
        
        $track = $this->trackRepository->find($data['ratedTrackId'] ?? null);
        if (!$track) {
            return $this->json(['error' => 'Track not found'], Response::HTTP_BAD_REQUEST);
        }
        
        $existingRating = $this->trackRatingRepository->findOneBy([
            'ratingUser' => $user,
            'ratedTrack' => $track
        ]);
        if ($existingRating) {
            return $this->json(['error' => 'User has already rated this track'], Response::HTTP_BAD_REQUEST);
        }
        
        $trackRating = new TrackRating();
        $trackRating->setRatingUser($user);
        $trackRating->setRatedTrack($track);
        $trackRating->setRateType($data['rateType'] ?? '');
        
        if (isset($data['ratedAt'])) {
            $trackRating->setRatedAt(new \DateTime($data['ratedAt']));
        }
        
        $errors = $this->validator->validate($trackRating);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }
        
        try {
            $this->entityManager->persist($trackRating);
            $this->entityManager->flush();
            
            return $this->json([
                'ratingUser' => [
                    'id' => $trackRating->getRatingUser()?->getId(),
                    'username' => $trackRating->getRatingUser()?->getUsername()
                ],
                'ratedTrack' => [
                    'id' => $trackRating->getRatedTrack()?->getId(),
                    'name' => $trackRating->getRatedTrack()?->getName()
                ],
                'rateType' => $trackRating->getRateType(),
                'ratedAt' => $trackRating->getRatedAt()?->format('Y-m-d H:i:s')
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/user/{userId}/track/{trackId}', methods: ['GET'])]
    public function show(int $userId, int $trackId): JsonResponse
    {
        $user = $this->userRepository->find($userId);
        if (!$user) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }
        
        $track = $this->trackRepository->find($trackId);
        if (!$track) {
            return $this->json(['error' => 'Track not found'], Response::HTTP_NOT_FOUND);
        }
        
        $trackRating = $this->trackRatingRepository->findOneBy([
            'ratingUser' => $user,
            'ratedTrack' => $track
        ]);
        
        if (!$trackRating) {
            return $this->json(['error' => 'Track rating not found'], Response::HTTP_NOT_FOUND);
        }
        
        return $this->json([
            'ratingUser' => [
                'id' => $trackRating->getRatingUser()?->getId(),
                'username' => $trackRating->getRatingUser()?->getUsername()
            ],
            'ratedTrack' => [
                'id' => $trackRating->getRatedTrack()?->getId(),
                'name' => $trackRating->getRatedTrack()?->getName()
            ],
            'rateType' => $trackRating->getRateType(),
            'ratedAt' => $trackRating->getRatedAt()?->format('Y-m-d H:i:s')
        ]);
    }

    #[Route('/user/{userId}/track/{trackId}', methods: ['PUT'])]
    public function update(int $userId, int $trackId, Request $request): JsonResponse
    {
        $user = $this->userRepository->find($userId);
        if (!$user) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }
        
        $track = $this->trackRepository->find($trackId);
        if (!$track) {
            return $this->json(['error' => 'Track not found'], Response::HTTP_NOT_FOUND);
        }
        
        $trackRating = $this->trackRatingRepository->findOneBy([
            'ratingUser' => $user,
            'ratedTrack' => $track
        ]);
        
        if (!$trackRating) {
            return $this->json(['error' => 'Track rating not found'], Response::HTTP_NOT_FOUND);
        }
        
        $data = json_decode($request->getContent(), true);
        
        if (isset($data['rateType'])) {
            $trackRating->setRateType($data['rateType']);
        }
        if (isset($data['ratedAt'])) {
            $trackRating->setRatedAt(new \DateTime($data['ratedAt']));
        }
        
        $errors = $this->validator->validate($trackRating);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }
        
        try {
            $this->entityManager->flush();
            
            return $this->json([
                'ratingUser' => [
                    'id' => $trackRating->getRatingUser()?->getId(),
                    'username' => $trackRating->getRatingUser()?->getUsername()
                ],
                'ratedTrack' => [
                    'id' => $trackRating->getRatedTrack()?->getId(),
                    'name' => $trackRating->getRatedTrack()?->getName()
                ],
                'rateType' => $trackRating->getRateType(),
                'ratedAt' => $trackRating->getRatedAt()?->format('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/user/{userId}/track/{trackId}', methods: ['DELETE'])]
    public function delete(int $userId, int $trackId): JsonResponse
    {
        $user = $this->userRepository->find($userId);
        if (!$user) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }
        
        $track = $this->trackRepository->find($trackId);
        if (!$track) {
            return $this->json(['error' => 'Track not found'], Response::HTTP_NOT_FOUND);
        }
        
        $trackRating = $this->trackRatingRepository->findOneBy([
            'ratingUser' => $user,
            'ratedTrack' => $track
        ]);
        
        if (!$trackRating) {
            return $this->json(['error' => 'Track rating not found'], Response::HTTP_NOT_FOUND);
        }
        
        try {
            $this->entityManager->remove($trackRating);
            $this->entityManager->flush();
            return $this->json(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}