<?php

namespace App\Controller;

use App\Entity\Company;
use App\Repository\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/companies')]
class CompanyController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer,
        private CompanyRepository $companyRepository
    ) {
    }

    #[Route('', name: 'company_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $companies = $this->companyRepository->findBy([], ['createdAt' => 'DESC']);
        
        return new JsonResponse(
            $this->serializer->serialize($companies, 'json', ['groups' => ['company:read']]),
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/{id}', name: 'company_show', methods: ['GET'])]
    public function show(Company $company): JsonResponse
    {
        return new JsonResponse(
            $this->serializer->serialize($company, 'json', ['groups' => ['company:read']]),
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/industry/{industry}', name: 'company_by_industry', methods: ['GET'])]
    public function byIndustry(string $industry): JsonResponse
    {
        $companies = $this->companyRepository->findByIndustry($industry);
        
        return new JsonResponse(
            $this->serializer->serialize($companies, 'json', ['groups' => ['company:read']]),
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/stage/{stage}', name: 'company_by_stage', methods: ['GET'])]
    public function byStage(string $stage): JsonResponse
    {
        $companies = $this->companyRepository->findByStage($stage);
        
        return new JsonResponse(
            $this->serializer->serialize($companies, 'json', ['groups' => ['company:read']]),
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/popular', name: 'company_popular', methods: ['GET'])]
    public function popular(): JsonResponse
    {
        $companies = $this->companyRepository->findPopular();
        
        return new JsonResponse(
            $this->serializer->serialize($companies, 'json', ['groups' => ['company:read']]),
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/{id}/follow', name: 'company_follow', methods: ['POST'])]
    public function follow(Company $company): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Authentication required'], Response::HTTP_UNAUTHORIZED);
        }

        $user->addFollowedCompany($company);
        $this->entityManager->flush();

        return new JsonResponse(['status' => 'followed'], Response::HTTP_OK);
    }

    #[Route('/{id}/unfollow', name: 'company_unfollow', methods: ['POST'])]
    public function unfollow(Company $company): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Authentication required'], Response::HTTP_UNAUTHORIZED);
        }

        $user->removeFollowedCompany($company);
        $this->entityManager->flush();

        return new JsonResponse(['status' => 'unfollowed'], Response::HTTP_OK);
    }
}