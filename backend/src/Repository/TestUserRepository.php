<?php

namespace App\Repository;

use App\Entity\TestUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TestUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TestUser::class);
    }

    public function save(TestUser $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TestUser $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByEmail(string $email): ?TestUser
    {
        return $this->findOneBy(['email' => $email]);
    }

    public function findByAgeRange(int $minAge, int $maxAge): array
    {
        return $this->createQueryBuilder('tu')
            ->andWhere('tu.age >= :minAge')
            ->andWhere('tu.age <= :maxAge')
            ->setParameter('minAge', $minAge)
            ->setParameter('maxAge', $maxAge)
            ->orderBy('tu.age', 'ASC')
            ->getQuery()
            ->getResult();
    }
}