<?php

namespace App\Repository;

use App\Entity\Album;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AlbumRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Album::class);
    }

    public function findAll(): array
    {
        return $this->createQueryBuilder('a')
            ->select('a', 'ar', 'ta', 'alr')
            ->leftJoin('a.artist', 'ar')
            ->leftJoin('a.trackAlbums', 'ta')
            ->leftJoin('a.albumRatings', 'alr')
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOneById(int $id): ?Album
    {
        return $this->createQueryBuilder('a')
            ->select('a', 'ar', 'ta', 'alr')
            ->leftJoin('a.artist', 'ar')
            ->leftJoin('a.trackAlbums', 'ta')
            ->leftJoin('a.albumRatings', 'alr')
            ->where('a.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByArtist(int $artistId): array
    {
        return $this->createQueryBuilder('a')
            ->select('a', 'ar')
            ->leftJoin('a.artist', 'ar')
            ->where('ar.id = :artistId')
            ->setParameter('artistId', $artistId)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}