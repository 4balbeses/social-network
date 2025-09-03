<?php

namespace App\Repository;

use App\Entity\PitchComment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PitchComment>
 */
class PitchCommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PitchComment::class);
    }

    public function findByPitch(int $pitchId): array
    {
        return $this->createQueryBuilder('pc')
            ->andWhere('pc.pitch = :pitch')
            ->setParameter('pitch', $pitchId)
            ->orderBy('pc.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByAuthor(int $authorId): array
    {
        return $this->createQueryBuilder('pc')
            ->andWhere('pc.author = :author')
            ->setParameter('author', $authorId)
            ->orderBy('pc.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}