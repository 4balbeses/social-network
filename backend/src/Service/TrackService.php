<?php

namespace App\Service;

use App\Entity\Track;
use App\DTO\Request\TrackCreateRequest;
use App\DTO\Request\TrackUpdateRequest;
use App\Repository\TrackRepository;
use App\Repository\MediaRepository;
use App\Repository\GenreRepository;
use Doctrine\ORM\EntityManagerInterface;

class TrackService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TrackRepository $trackRepository,
        private MediaRepository $mediaRepository,
        private GenreRepository $genreRepository
    ) {
    }

    public function createTrack(TrackCreateRequest $request): Track
    {
        $media = $this->mediaRepository->find($request->trackFileId);
        if (!$media) {
            throw new \InvalidArgumentException('Media file not found');
        }
        
        $genre = $this->genreRepository->find($request->genreId);
        if (!$genre) {
            throw new \InvalidArgumentException('Genre not found');
        }
        
        $track = new Track();
        $track->setName($request->name);
        $track->setDescription($request->description);
        $track->setTrackFile($media);
        $track->setGenre($genre);
        
        $this->entityManager->persist($track);
        $this->entityManager->flush();
        
        return $track;
    }

    public function updateTrack(Track $track, TrackUpdateRequest $request): Track
    {
        if ($request->name !== null) {
            $track->setName($request->name);
        }
        
        if ($request->description !== null) {
            $track->setDescription($request->description);
        }
        
        if ($request->trackFileId !== null) {
            $media = $this->mediaRepository->find($request->trackFileId);
            if (!$media) {
                throw new \InvalidArgumentException('Media file not found');
            }
            $track->setTrackFile($media);
        }
        
        if ($request->genreId !== null) {
            $genre = $this->genreRepository->find($request->genreId);
            if (!$genre) {
                throw new \InvalidArgumentException('Genre not found');
            }
            $track->setGenre($genre);
        }
        
        $this->entityManager->flush();
        
        return $track;
    }

    public function deleteTrack(Track $track): void
    {
        $this->entityManager->remove($track);
        $this->entityManager->flush();
    }

    public function getAllTracks(): array
    {
        return $this->trackRepository->findAll();
    }

    public function getTrackById(int $id): ?Track
    {
        return $this->trackRepository->findOneById($id);
    }

    public function getTracksByGenre(int $genreId): array
    {
        return $this->trackRepository->findByGenre($genreId);
    }

    public function searchTracksByName(string $searchTerm): array
    {
        return $this->trackRepository->findByName($searchTerm);
    }

    public function getTopRatedTracks(int $limit = 10): array
    {
        return $this->trackRepository->findTopRated($limit);
    }
}