<?php

namespace App\Service;

use App\Entity\Tag;
use App\Entity\User;
use App\DTO\Request\TagCreateRequest;
use App\DTO\Request\TagUpdateRequest;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;

class TagService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TagRepository $tagRepository
    ) {
    }

    public function createTag(TagCreateRequest $request, User $author): Tag
    {
        $tag = new Tag();
        $tag->setName($request->name);
        $tag->setAuthor($author);
        
        $this->entityManager->persist($tag);
        $this->entityManager->flush();
        
        return $tag;
    }

    public function updateTag(Tag $tag, TagUpdateRequest $request): Tag
    {
        if ($request->name !== null) {
            $tag->setName($request->name);
        }
        
        $this->entityManager->flush();
        
        return $tag;
    }

    public function deleteTag(Tag $tag): void
    {
        $this->entityManager->remove($tag);
        $this->entityManager->flush();
    }

    public function getAllTags(): array
    {
        return $this->tagRepository->findAll();
    }

    public function getTagById(int $id): ?Tag
    {
        return $this->tagRepository->find($id);
    }

    public function getTagsByAuthor(User $author): array
    {
        return $this->tagRepository->findBy(['author' => $author]);
    }
}