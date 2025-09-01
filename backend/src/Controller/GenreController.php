<?php

namespace App\Controller;

use App\Service\GenreService;
use App\Service\MapperService;
use App\Dto\Genre\Request\GenreCreateRequest;
use App\Dto\Genre\Request\GenreUpdateRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/genres')]
class GenreController extends AbstractController
{
    public function __construct(
        private GenreService $genreService,
        private MapperService $mapperService,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $genres = $this->genreService->getAllGenres();
        $responseData = [];
        
        foreach ($genres as $genre) {
            $responseData[] = $this->mapperService->genreToResponse($genre);
        }
        
        return $this->json($responseData);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $createRequest = new GenreCreateRequest();
        $createRequest->name = $data['name'] ?? '';
        $createRequest->description = $data['description'] ?? null;
        
        $errors = $this->validator->validate($createRequest);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }
        
        try {
            $genre = $this->genreService->createGenre($createRequest);
            return $this->json($this->mapperService->genreToResponse($genre), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $genre = $this->genreService->getGenreById($id);
        
        if (!$genre) {
            return $this->json(['error' => 'Genre not found'], Response::HTTP_NOT_FOUND);
        }
        
        return $this->json($this->mapperService->genreToResponse($genre));
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $genre = $this->genreService->getGenreById($id);
        
        if (!$genre) {
            return $this->json(['error' => 'Genre not found'], Response::HTTP_NOT_FOUND);
        }
        
        $data = json_decode($request->getContent(), true);
        
        $updateRequest = new GenreUpdateRequest();
        $updateRequest->name = $data['name'] ?? null;
        $updateRequest->description = $data['description'] ?? null;
        
        $errors = $this->validator->validate($updateRequest);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }
        
        try {
            $updatedGenre = $this->genreService->updateGenre($genre, $updateRequest);
            return $this->json($this->mapperService->genreToResponse($updatedGenre));
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $genre = $this->genreService->getGenreById($id);
        
        if (!$genre) {
            return $this->json(['error' => 'Genre not found'], Response::HTTP_NOT_FOUND);
        }
        
        try {
            $this->genreService->deleteGenre($genre);
            return $this->json(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}