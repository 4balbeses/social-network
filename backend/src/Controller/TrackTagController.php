<?php

namespace App\Controller;

use App\Entity\TrackTag;
use App\Repository\TrackTagRepository;
use App\Repository\TrackRepository;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/track-tags')]
class TrackTagController extends AbstractController
{
    public function __construct(
        private TrackTagRepository $trackTagRepository,
        private TrackRepository $trackRepository,
        private TagRepository $tagRepository,
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $trackTags = $this->trackTagRepository->findAll();
        $responseData = [];
        
        foreach ($trackTags as $trackTag) {
            $responseData[] = [
                'track' => [
                    'id' => $trackTag->getTrack()?->getId(),
                    'name' => $trackTag->getTrack()?->getName()
                ],
                'tag' => [
                    'id' => $trackTag->getTag()?->getId(),
                    'name' => $trackTag->getTag()?->getName()
                ],
                'addedAt' => $trackTag->getAddedAt()?->format('Y-m-d H:i:s')
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
        
        $tag = $this->tagRepository->find($data['tagId'] ?? null);
        if (!$tag) {
            return $this->json(['error' => 'Tag not found'], Response::HTTP_BAD_REQUEST);
        }
        
        $existingTrackTag = $this->trackTagRepository->findOneBy([
            'track' => $track,
            'tag' => $tag
        ]);
        if ($existingTrackTag) {
            return $this->json(['error' => 'Track-Tag relationship already exists'], Response::HTTP_BAD_REQUEST);
        }
        
        $trackTag = new TrackTag();
        $trackTag->setTrack($track);
        $trackTag->setTag($tag);
        
        if (isset($data['addedAt'])) {
            $trackTag->setAddedAt(new \DateTime($data['addedAt']));
        }
        
        $errors = $this->validator->validate($trackTag);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }
        
        try {
            $this->entityManager->persist($trackTag);
            $this->entityManager->flush();
            
            return $this->json([
                'track' => [
                    'id' => $trackTag->getTrack()?->getId(),
                    'name' => $trackTag->getTrack()?->getName()
                ],
                'tag' => [
                    'id' => $trackTag->getTag()?->getId(),
                    'name' => $trackTag->getTag()?->getName()
                ],
                'addedAt' => $trackTag->getAddedAt()?->format('Y-m-d H:i:s')
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/track/{trackId}/tag/{tagId}', methods: ['GET'])]
    public function show(int $trackId, int $tagId): JsonResponse
    {
        $track = $this->trackRepository->find($trackId);
        if (!$track) {
            return $this->json(['error' => 'Track not found'], Response::HTTP_NOT_FOUND);
        }
        
        $tag = $this->tagRepository->find($tagId);
        if (!$tag) {
            return $this->json(['error' => 'Tag not found'], Response::HTTP_NOT_FOUND);
        }
        
        $trackTag = $this->trackTagRepository->findOneBy([
            'track' => $track,
            'tag' => $tag
        ]);
        
        if (!$trackTag) {
            return $this->json(['error' => 'Track-Tag relationship not found'], Response::HTTP_NOT_FOUND);
        }
        
        return $this->json([
            'track' => [
                'id' => $trackTag->getTrack()?->getId(),
                'name' => $trackTag->getTrack()?->getName()
            ],
            'tag' => [
                'id' => $trackTag->getTag()?->getId(),
                'name' => $trackTag->getTag()?->getName()
            ],
            'addedAt' => $trackTag->getAddedAt()?->format('Y-m-d H:i:s')
        ]);
    }

    #[Route('/track/{trackId}/tag/{tagId}', methods: ['PUT'])]
    public function update(int $trackId, int $tagId, Request $request): JsonResponse
    {
        $track = $this->trackRepository->find($trackId);
        if (!$track) {
            return $this->json(['error' => 'Track not found'], Response::HTTP_NOT_FOUND);
        }
        
        $tag = $this->tagRepository->find($tagId);
        if (!$tag) {
            return $this->json(['error' => 'Tag not found'], Response::HTTP_NOT_FOUND);
        }
        
        $trackTag = $this->trackTagRepository->findOneBy([
            'track' => $track,
            'tag' => $tag
        ]);
        
        if (!$trackTag) {
            return $this->json(['error' => 'Track-Tag relationship not found'], Response::HTTP_NOT_FOUND);
        }
        
        $data = json_decode($request->getContent(), true);
        
        if (isset($data['addedAt'])) {
            $trackTag->setAddedAt(new \DateTime($data['addedAt']));
        }
        
        $errors = $this->validator->validate($trackTag);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }
        
        try {
            $this->entityManager->flush();
            
            return $this->json([
                'track' => [
                    'id' => $trackTag->getTrack()?->getId(),
                    'name' => $trackTag->getTrack()?->getName()
                ],
                'tag' => [
                    'id' => $trackTag->getTag()?->getId(),
                    'name' => $trackTag->getTag()?->getName()
                ],
                'addedAt' => $trackTag->getAddedAt()?->format('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/track/{trackId}/tag/{tagId}', methods: ['DELETE'])]
    public function delete(int $trackId, int $tagId): JsonResponse
    {
        $track = $this->trackRepository->find($trackId);
        if (!$track) {
            return $this->json(['error' => 'Track not found'], Response::HTTP_NOT_FOUND);
        }
        
        $tag = $this->tagRepository->find($tagId);
        if (!$tag) {
            return $this->json(['error' => 'Tag not found'], Response::HTTP_NOT_FOUND);
        }
        
        $trackTag = $this->trackTagRepository->findOneBy([
            'track' => $track,
            'tag' => $tag
        ]);
        
        if (!$trackTag) {
            return $this->json(['error' => 'Track-Tag relationship not found'], Response::HTTP_NOT_FOUND);
        }
        
        try {
            $this->entityManager->remove($trackTag);
            $this->entityManager->flush();
            return $this->json(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}