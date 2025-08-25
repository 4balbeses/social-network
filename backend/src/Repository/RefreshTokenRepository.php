<?php

namespace App\Repository;

use App\Entity\RefreshToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RefreshTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RefreshToken::class);
    }

    public function purgeExpired(): int
    {
        return $this->createQueryBuilder("r")
            ->delete()
            ->where("r.expiresAt <= :now")
            ->setParameter("now", new \DateTimeImmutable())
            ->getQuery()->execute();
    }
}
