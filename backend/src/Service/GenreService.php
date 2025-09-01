<?php

namespace App\Service;

use App\Entity\Genre;
use App\DTO\Request\GenreCreateRequest;
use App\DTO\Request\GenreUpdateRequest;
use App\Repository\GenreRepository;
use Doctrine\ORM\EntityManagerInterface;

class GenreService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private GenreRepository $genreRepository
    ) {
    }

    public function createGenre(GenreCreateRequest $request): Genre
    {
        $genre = new Genre();
        $genre->setName($request->name);
        $genre->setDescription($request->description);
        
        $this->entityManager->persist($genre);
        $this->entityManager->flush();
        
        return $genre;
    }

    public function updateGenre(Genre $genre, GenreUpdateRequest $request): Genre
    {
        if ($request->name !== null) {
            $genre->setName($request->name);
        }
        
        if ($request->description !== null) {
            $genre->setDescription($request->description);
        }
        
        $this->entityManager->flush();
        
        return $genre;
    }

    public function deleteGenre(Genre $genre): void
    {
        $this->entityManager->remove($genre);
        $this->entityManager->flush();
    }

    public function getAllGenres(): array
    {
        return $this->genreRepository->findAll();
    }

    public function getGenreById(int $id): ?Genre
    {
        return $this->genreRepository->find($id);
    }
}