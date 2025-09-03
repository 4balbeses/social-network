<?php

namespace App\Repository;

use App\Entity\Investment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Investment>
 */
class InvestmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Investment::class);
    }

    public function findByInvestor(int $investorId): array
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.investor = :investor')
            ->setParameter('investor', $investorId)
            ->orderBy('i.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByCompany(int $companyId): array
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.company = :company')
            ->setParameter('company', $companyId)
            ->orderBy('i.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByStatus(string $status): array
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.status = :status')
            ->setParameter('status', $status)
            ->orderBy('i.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getTotalInvestmentByCompany(int $companyId): string
    {
        $result = $this->createQueryBuilder('i')
            ->select('SUM(i.amount) as total')
            ->andWhere('i.company = :company')
            ->andWhere('i.status = :status')
            ->setParameter('company', $companyId)
            ->setParameter('status', 'completed')
            ->getQuery()
            ->getSingleScalarResult();

        return $result ?? '0';
    }
}