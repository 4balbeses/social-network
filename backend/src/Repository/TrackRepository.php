<?php

namespace App\Repository;

use App\Entity\Track;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TrackRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Track::class);
    }

    public function findAll(): array
    {
        return $this->createQueryBuilder('t')
            ->select('t', 'tf', 'g', 'ta', 'tp', 'tt', 'tr')
            ->leftJoin('t.trackFile', 'tf')
            ->leftJoin('t.genre', 'g')
            ->leftJoin('t.trackAlbums', 'ta')
            ->leftJoin('t.trackPlaylists', 'tp')
            ->leftJoin('t.trackTags', 'tt')
            ->leftJoin('t.trackRatings', 'tr')
            ->orderBy('t.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOneById(int $id): ?Track
    {
        return $this->createQueryBuilder('t')
            ->select('t', 'tf', 'g', 'ta', 'tp', 'tt', 'tr')
            ->leftJoin('t.trackFile', 'tf')
            ->leftJoin('t.genre', 'g')
            ->leftJoin('t.trackAlbums', 'ta')
            ->leftJoin('t.trackPlaylists', 'tp')
            ->leftJoin('t.trackTags', 'tt')
            ->leftJoin('t.trackRatings', 'tr')
            ->where('t.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByGenre(int $genreId): array
    {
        return $this->createQueryBuilder('t')
            ->select('t', 'tf', 'g')
            ->leftJoin('t.trackFile', 'tf')
            ->leftJoin('t.genre', 'g')
            ->where('g.id = :genreId')
            ->setParameter('genreId', $genreId)
            ->orderBy('t.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByName(string $searchTerm): array
    {
        return $this->createQueryBuilder('t')
            ->select('t', 'tf', 'g')
            ->leftJoin('t.trackFile', 'tf')
            ->leftJoin('t.genre', 'g')
            ->where('t.name LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $searchTerm . '%')
            ->orderBy('t.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findTopRated(int $limit = 10): array
    {
        return $this->createQueryBuilder('t')
            ->select('t', 'tf', 'g', 'COUNT(tr.rateType) as ratingCount')
            ->leftJoin('t.trackFile', 'tf')
            ->leftJoin('t.genre', 'g')
            ->leftJoin('t.trackRatings', 'tr')
            ->where('tr.rateType = :rateType')
            ->setParameter('rateType', 'like')
            ->groupBy('t.id')
            ->orderBy('ratingCount', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}