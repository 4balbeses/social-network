<?php

namespace App\Repository;

use App\Entity\Company;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Company>
 */
class CompanyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Company::class);
    }

    public function findByIndustry(string $industry): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.industry = :industry')
            ->setParameter('industry', $industry)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByStage(string $stage): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.stage = :stage')
            ->setParameter('stage', $stage)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findPopular(int $limit = 10): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.followers', 'f')
            ->groupBy('c.id')
            ->orderBy('COUNT(f.id)', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}