<?php

namespace App\Service;

use App\Entity\Playlist;
use App\Entity\User;
use App\DTO\Request\PlaylistCreateRequest;
use App\DTO\Request\PlaylistCreateRequest as PlaylistUpdateRequest;
use App\Repository\PlaylistRepository;
use Doctrine\ORM\EntityManagerInterface;

class PlaylistService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PlaylistRepository $playlistRepository
    ) {
    }

    public function createPlaylist(PlaylistCreateRequest $request, User $owner): Playlist
    {
        $playlist = new Playlist();
        $playlist->setName($request->name);
        $playlist->setDescription($request->description);
        $playlist->setIsPublic($request->isPublic);
        $playlist->setOwner($owner);
        
        $this->entityManager->persist($playlist);
        $this->entityManager->flush();
        
        return $playlist;
    }

    public function updatePlaylist(Playlist $playlist, PlaylistUpdateRequest $request): Playlist
    {
        if ($request->name !== null) {
            $playlist->setName($request->name);
        }
        
        if ($request->description !== null) {
            $playlist->setDescription($request->description);
        }
        
        if (isset($request->isPublic)) {
            $playlist->setIsPublic($request->isPublic);
        }
        
        $this->entityManager->flush();
        
        return $playlist;
    }

    public function deletePlaylist(Playlist $playlist): void
    {
        $this->entityManager->remove($playlist);
        $this->entityManager->flush();
    }

    public function getAllPlaylists(): array
    {
        return $this->playlistRepository->findAll();
    }

    public function getPlaylistById(int $id): ?Playlist
    {
        return $this->playlistRepository->findOneById($id);
    }

    public function getPlaylistsByOwner(User $owner): array
    {
        return $this->playlistRepository->findByOwner($owner);
    }

    public function getPublicPlaylists(): array
    {
        return $this->playlistRepository->findPublicPlaylists();
    }
}