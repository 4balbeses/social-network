<?php

namespace App\Service;

use App\Entity\Album;
use App\DTO\Request\AlbumCreateRequest;
use App\DTO\Request\AlbumUpdateRequest;
use App\Repository\AlbumRepository;
use App\Repository\ArtistRepository;
use Doctrine\ORM\EntityManagerInterface;

class AlbumService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AlbumRepository $albumRepository,
        private ArtistRepository $artistRepository
    ) {
    }

    public function createAlbum(AlbumCreateRequest $request): Album
    {
        $artist = $this->artistRepository->find($request->artistId);
        if (!$artist) {
            throw new \InvalidArgumentException('Artist not found');
        }
        
        $album = new Album();
        $album->setName($request->name);
        $album->setDescription($request->description);
        $album->setArtist($artist);
        
        $this->entityManager->persist($album);
        $this->entityManager->flush();
        
        return $album;
    }

    public function updateAlbum(Album $album, AlbumUpdateRequest $request): Album
    {
        if ($request->name !== null) {
            $album->setName($request->name);
        }
        
        if ($request->description !== null) {
            $album->setDescription($request->description);
        }
        
        if ($request->artistId !== null) {
            $artist = $this->artistRepository->find($request->artistId);
            if (!$artist) {
                throw new \InvalidArgumentException('Artist not found');
            }
            $album->setArtist($artist);
        }
        
        $this->entityManager->flush();
        
        return $album;
    }

    public function deleteAlbum(Album $album): void
    {
        $this->entityManager->remove($album);
        $this->entityManager->flush();
    }

    public function getAllAlbums(): array
    {
        return $this->albumRepository->findAll();
    }

    public function getAlbumById(int $id): ?Album
    {
        return $this->albumRepository->findOneById($id);
    }

    public function getAlbumsByArtist(int $artistId): array
    {
        return $this->albumRepository->findByArtist($artistId);
    }
}