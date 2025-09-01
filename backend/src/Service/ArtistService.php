<?php

namespace App\Service;

use App\Entity\Artist;
use App\DTO\Request\ArtistCreateRequest;
use App\DTO\Request\ArtistUpdateRequest;
use App\Repository\ArtistRepository;
use Doctrine\ORM\EntityManagerInterface;

class ArtistService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ArtistRepository $artistRepository
    ) {
    }

    public function createArtist(ArtistCreateRequest $request): Artist
    {
        $artist = new Artist();
        $artist->setFullName($request->fullName);
        $artist->setDescription($request->description);
        
        $this->entityManager->persist($artist);
        $this->entityManager->flush();
        
        return $artist;
    }

    public function updateArtist(Artist $artist, ArtistUpdateRequest $request): Artist
    {
        if ($request->fullName !== null) {
            $artist->setFullName($request->fullName);
        }
        
        if ($request->description !== null) {
            $artist->setDescription($request->description);
        }
        
        $this->entityManager->flush();
        
        return $artist;
    }

    public function deleteArtist(Artist $artist): void
    {
        $this->entityManager->remove($artist);
        $this->entityManager->flush();
    }

    public function getAllArtists(): array
    {
        return $this->artistRepository->findAll();
    }

    public function getArtistById(int $id): ?Artist
    {
        return $this->artistRepository->find($id);
    }
}