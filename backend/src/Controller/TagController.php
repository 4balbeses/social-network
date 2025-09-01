<?php

namespace App\Controller;

use App\Service\TagService;
use App\Service\MapperService;
use App\Service\UserService;
use App\Dto\Tag\Request\TagCreateRequest;
use App\Dto\Tag\Request\TagUpdateRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/tags')]
class TagController extends AbstractController
{
    public function __construct(
        private TagService $tagService,
        private MapperService $mapperService,
        private UserService $userService,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $tags = $this->tagService->getAllTags();
        $responseData = [];
        
        foreach ($tags as $tag) {
            $responseData[] = $this->mapperService->tagToResponse($tag);
        }
        
        return $this->json($responseData);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $createRequest = new TagCreateRequest();
        $createRequest->name = $data['name'] ?? '';
        
        $errors = $this->validator->validate($createRequest);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }
        
        if (!isset($data['authorId'])) {
            return $this->json(['error' => 'Author ID is required'], Response::HTTP_BAD_REQUEST);
        }
        
        $author = $this->userService->getUserById($data['authorId']);
        if (!$author) {
            return $this->json(['error' => 'Author not found'], Response::HTTP_BAD_REQUEST);
        }
        
        try {
            $tag = $this->tagService->createTag($createRequest, $author);
            return $this->json($this->mapperService->tagToResponse($tag), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $tag = $this->tagService->getTagById($id);
        
        if (!$tag) {
            return $this->json(['error' => 'Tag not found'], Response::HTTP_NOT_FOUND);
        }
        
        return $this->json($this->mapperService->tagToResponse($tag));
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $tag = $this->tagService->getTagById($id);
        
        if (!$tag) {
            return $this->json(['error' => 'Tag not found'], Response::HTTP_NOT_FOUND);
        }
        
        $data = json_decode($request->getContent(), true);
        
        $updateRequest = new TagUpdateRequest();
        $updateRequest->name = $data['name'] ?? null;
        
        $errors = $this->validator->validate($updateRequest);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }
        
        try {
            $updatedTag = $this->tagService->updateTag($tag, $updateRequest);
            return $this->json($this->mapperService->tagToResponse($updatedTag));
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $tag = $this->tagService->getTagById($id);
        
        if (!$tag) {
            return $this->json(['error' => 'Tag not found'], Response::HTTP_NOT_FOUND);
        }
        
        try {
            $this->tagService->deleteTag($tag);
            return $this->json(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}