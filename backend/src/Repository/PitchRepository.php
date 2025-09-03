<?php

namespace App\Repository;

use App\Entity\Pitch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Pitch>
 */
class PitchRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pitch::class);
    }

    public function findActivePitches(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByCompany(int $companyId): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.company = :company')
            ->setParameter('company', $companyId)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findTrending(int $limit = 10): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.likedBy', 'l')
            ->leftJoin('p.comments', 'c')
            ->groupBy('p.id')
            ->orderBy('COUNT(l.id) + COUNT(c.id)', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByFundingGoalRange(string $minAmount, string $maxAmount): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.fundingGoal >= :minAmount')
            ->andWhere('p.fundingGoal <= :maxAmount')
            ->setParameter('minAmount', $minAmount)
            ->setParameter('maxAmount', $maxAmount)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}