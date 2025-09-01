<?php

namespace App\Controller;

use App\Entity\ArtistAlbum;
use App\Repository\ArtistAlbumRepository;
use App\Repository\ArtistRepository;
use App\Repository\AlbumRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/artist-albums')]
class ArtistAlbumController extends AbstractController
{
    public function __construct(
        private ArtistAlbumRepository $artistAlbumRepository,
        private ArtistRepository $artistRepository,
        private AlbumRepository $albumRepository,
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $artistAlbums = $this->artistAlbumRepository->findAll();
        $responseData = [];
        
        foreach ($artistAlbums as $artistAlbum) {
            $responseData[] = [
                'artist' => [
                    'id' => $artistAlbum->getArtist()?->getId(),
                    'fullName' => $artistAlbum->getArtist()?->getFullName()
                ],
                'album' => [
                    'id' => $artistAlbum->getAlbum()?->getId(),
                    'name' => $artistAlbum->getAlbum()?->getName()
                ],
                'addedAt' => $artistAlbum->getAddedAt()?->format('Y-m-d H:i:s')
            ];
        }
        
        return $this->json($responseData);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $artist = $this->artistRepository->find($data['artistId'] ?? null);
        if (!$artist) {
            return $this->json(['error' => 'Artist not found'], Response::HTTP_BAD_REQUEST);
        }
        
        $album = $this->albumRepository->find($data['albumId'] ?? null);
        if (!$album) {
            return $this->json(['error' => 'Album not found'], Response::HTTP_BAD_REQUEST);
        }
        
        $existingArtistAlbum = $this->artistAlbumRepository->findOneBy([
            'artist' => $artist,
            'album' => $album
        ]);
        if ($existingArtistAlbum) {
            return $this->json(['error' => 'Artist-Album relationship already exists'], Response::HTTP_BAD_REQUEST);
        }
        
        $artistAlbum = new ArtistAlbum();
        $artistAlbum->setArtist($artist);
        $artistAlbum->setAlbum($album);
        
        if (isset($data['addedAt'])) {
            $artistAlbum->setAddedAt(new \DateTime($data['addedAt']));
        }
        
        $errors = $this->validator->validate($artistAlbum);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }
        
        try {
            $this->entityManager->persist($artistAlbum);
            $this->entityManager->flush();
            
            return $this->json([
                'artist' => [
                    'id' => $artistAlbum->getArtist()?->getId(),
                    'fullName' => $artistAlbum->getArtist()?->getFullName()
                ],
                'album' => [
                    'id' => $artistAlbum->getAlbum()?->getId(),
                    'name' => $artistAlbum->getAlbum()?->getName()
                ],
                'addedAt' => $artistAlbum->getAddedAt()?->format('Y-m-d H:i:s')
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/artist/{artistId}/album/{albumId}', methods: ['GET'])]
    public function show(int $artistId, int $albumId): JsonResponse
    {
        $artist = $this->artistRepository->find($artistId);
        if (!$artist) {
            return $this->json(['error' => 'Artist not found'], Response::HTTP_NOT_FOUND);
        }
        
        $album = $this->albumRepository->find($albumId);
        if (!$album) {
            return $this->json(['error' => 'Album not found'], Response::HTTP_NOT_FOUND);
        }
        
        $artistAlbum = $this->artistAlbumRepository->findOneBy([
            'artist' => $artist,
            'album' => $album
        ]);
        
        if (!$artistAlbum) {
            return $this->json(['error' => 'Artist-Album relationship not found'], Response::HTTP_NOT_FOUND);
        }
        
        return $this->json([
            'artist' => [
                'id' => $artistAlbum->getArtist()?->getId(),
                'fullName' => $artistAlbum->getArtist()?->getFullName()
            ],
            'album' => [
                'id' => $artistAlbum->getAlbum()?->getId(),
                'name' => $artistAlbum->getAlbum()?->getName()
            ],
            'addedAt' => $artistAlbum->getAddedAt()?->format('Y-m-d H:i:s')
        ]);
    }

    #[Route('/artist/{artistId}/album/{albumId}', methods: ['PUT'])]
    public function update(int $artistId, int $albumId, Request $request): JsonResponse
    {
        $artist = $this->artistRepository->find($artistId);
        if (!$artist) {
            return $this->json(['error' => 'Artist not found'], Response::HTTP_NOT_FOUND);
        }
        
        $album = $this->albumRepository->find($albumId);
        if (!$album) {
            return $this->json(['error' => 'Album not found'], Response::HTTP_NOT_FOUND);
        }
        
        $artistAlbum = $this->artistAlbumRepository->findOneBy([
            'artist' => $artist,
            'album' => $album
        ]);
        
        if (!$artistAlbum) {
            return $this->json(['error' => 'Artist-Album relationship not found'], Response::HTTP_NOT_FOUND);
        }
        
        $data = json_decode($request->getContent(), true);
        
        if (isset($data['addedAt'])) {
            $artistAlbum->setAddedAt(new \DateTime($data['addedAt']));
        }
        
        $errors = $this->validator->validate($artistAlbum);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }
        
        try {
            $this->entityManager->flush();
            
            return $this->json([
                'artist' => [
                    'id' => $artistAlbum->getArtist()?->getId(),
                    'fullName' => $artistAlbum->getArtist()?->getFullName()
                ],
                'album' => [
                    'id' => $artistAlbum->getAlbum()?->getId(),
                    'name' => $artistAlbum->getAlbum()?->getName()
                ],
                'addedAt' => $artistAlbum->getAddedAt()?->format('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/artist/{artistId}/album/{albumId}', methods: ['DELETE'])]
    public function delete(int $artistId, int $albumId): JsonResponse
    {
        $artist = $this->artistRepository->find($artistId);
        if (!$artist) {
            return $this->json(['error' => 'Artist not found'], Response::HTTP_NOT_FOUND);
        }
        
        $album = $this->albumRepository->find($albumId);
        if (!$album) {
            return $this->json(['error' => 'Album not found'], Response::HTTP_NOT_FOUND);
        }
        
        $artistAlbum = $this->artistAlbumRepository->findOneBy([
            'artist' => $artist,
            'album' => $album
        ]);
        
        if (!$artistAlbum) {
            return $this->json(['error' => 'Artist-Album relationship not found'], Response::HTTP_NOT_FOUND);
        }
        
        try {
            $this->entityManager->remove($artistAlbum);
            $this->entityManager->flush();
            return $this->json(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}