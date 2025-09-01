<?php

namespace App\Repository;

use App\Entity\Playlist;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PlaylistRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Playlist::class);
    }

    public function findAll(): array
    {
        return $this->createQueryBuilder('p')
            ->select('p', 'o', 'tp', 'pr')
            ->leftJoin('p.owner', 'o')
            ->leftJoin('p.trackPlaylists', 'tp')
            ->leftJoin('p.playlistRatings', 'pr')
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOneById(int $id): ?Playlist
    {
        return $this->createQueryBuilder('p')
            ->select('p', 'o', 'tp', 'pr')
            ->leftJoin('p.owner', 'o')
            ->leftJoin('p.trackPlaylists', 'tp')
            ->leftJoin('p.playlistRatings', 'pr')
            ->where('p.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByOwner(User $owner): array
    {
        return $this->createQueryBuilder('p')
            ->select('p', 'o', 'tp')
            ->leftJoin('p.owner', 'o')
            ->leftJoin('p.trackPlaylists', 'tp')
            ->where('p.owner = :owner')
            ->setParameter('owner', $owner)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findPublicPlaylists(): array
    {
        return $this->createQueryBuilder('p')
            ->select('p', 'o', 'tp')
            ->leftJoin('p.owner', 'o')
            ->leftJoin('p.trackPlaylists', 'tp')
            ->where('p.isPublic = :isPublic')
            ->setParameter('isPublic', true)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}