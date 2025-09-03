<?php

namespace App\Controller;

use App\Entity\Pitch;
use App\Repository\PitchRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/pitches')]
class PitchController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer,
        private PitchRepository $pitchRepository
    ) {
    }

    #[Route('', name: 'pitch_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $pitches = $this->pitchRepository->findActivePitches();
        
        return new JsonResponse(
            $this->serializer->serialize($pitches, 'json', ['groups' => ['pitch:read']]),
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/{id}', name: 'pitch_show', methods: ['GET'])]
    public function show(Pitch $pitch): JsonResponse
    {
        return new JsonResponse(
            $this->serializer->serialize($pitch, 'json', ['groups' => ['pitch:read']]),
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/trending', name: 'pitch_trending', methods: ['GET'])]
    public function trending(): JsonResponse
    {
        $pitches = $this->pitchRepository->findTrending();
        
        return new JsonResponse(
            $this->serializer->serialize($pitches, 'json', ['groups' => ['pitch:read']]),
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/company/{companyId}', name: 'pitch_by_company', methods: ['GET'])]
    public function byCompany(int $companyId): JsonResponse
    {
        $pitches = $this->pitchRepository->findByCompany($companyId);
        
        return new JsonResponse(
            $this->serializer->serialize($pitches, 'json', ['groups' => ['pitch:read']]),
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/{id}/like', name: 'pitch_like', methods: ['POST'])]
    public function like(Pitch $pitch): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Authentication required'], Response::HTTP_UNAUTHORIZED);
        }

        $user->addLikedPitch($pitch);
        $this->entityManager->flush();

        return new JsonResponse(['status' => 'liked'], Response::HTTP_OK);
    }

    #[Route('/{id}/unlike', name: 'pitch_unlike', methods: ['POST'])]
    public function unlike(Pitch $pitch): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Authentication required'], Response::HTTP_UNAUTHORIZED);
        }

        $user->removeLikedPitch($pitch);
        $this->entityManager->flush();

        return new JsonResponse(['status' => 'unliked'], Response::HTTP_OK);
    }

    #[Route('/{id}/invest', name: 'pitch_invest', methods: ['POST'])]
    public function invest(Pitch $pitch, Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Authentication required'], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['amount']) || !is_numeric($data['amount'])) {
            return new JsonResponse(['error' => 'Valid investment amount required'], Response::HTTP_BAD_REQUEST);
        }

        // This would typically create an investment proposal
        // For now, just return success
        return new JsonResponse([
            'status' => 'investment_proposal_created',
            'amount' => $data['amount'],
            'pitch_id' => $pitch->getId()
        ], Response::HTTP_OK);
    }
}