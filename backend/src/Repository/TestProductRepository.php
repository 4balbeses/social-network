<?php

namespace App\Repository;

use App\Entity\TestProduct;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TestProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TestProduct::class);
    }

    public function save(TestProduct $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TestProduct $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findActiveProducts(): array
    {
        return $this->createQueryBuilder('tp')
            ->andWhere('tp.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('tp.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByPriceRange(string $minPrice, string $maxPrice): array
    {
        return $this->createQueryBuilder('tp')
            ->andWhere('tp.price >= :minPrice')
            ->andWhere('tp.price <= :maxPrice')
            ->andWhere('tp.isActive = :active')
            ->setParameter('minPrice', $minPrice)
            ->setParameter('maxPrice', $maxPrice)
            ->setParameter('active', true)
            ->orderBy('tp.price', 'ASC')
            ->getQuery()
            ->getResult();
    }
}