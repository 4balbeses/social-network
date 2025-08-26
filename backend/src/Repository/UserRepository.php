<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryProxy;
use Doctrine\Persistence\ManagerRegistry;

class UserRepository extends ServiceEntityRepositoryProxy
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Сохраняет пользователя в базе данных
     */
    public function save(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Удаляет пользователя из базы данных
     */
    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Находит пользователя по email
     */
    public function findByEmail(string $email): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Находит пользователя по username
     */
    public function findByUsername(string $username): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.username = :username')
            ->setParameter('username', $username)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Проверяет, существует ли пользователь с указанным email
     */
    public function emailExists(string $email): bool
    {
        return $this->createQueryBuilder('u')
                ->select('COUNT(u.id)')
                ->andWhere('u.email = :email')
                ->setParameter('email', $email)
                ->getQuery()
                ->getSingleScalarResult() > 0;
    }

    /**
     * Проверяет, существует ли пользователь с указанным username
     */
    public function usernameExists(string $username): bool
    {
        return $this->createQueryBuilder('u')
                ->select('COUNT(u.id)')
                ->andWhere('u.username = :username')
                ->setParameter('username', $username)
                ->getQuery()
                ->getSingleScalarResult() > 0;
    }

    /**
     * Находит пользователей по полному имени (поиск с частичным совпадением)
     */
    public function findByFullName(string $fullName): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.fullName LIKE :fullName')
            ->setParameter('fullName', '%' . $fullName . '%')
            ->getQuery()
            ->getResult();
    }

    /**
     * Находит пользователей, зарегистрированных после указанной даты
     */
    public function findRegisteredAfter(\DateTimeInterface $date): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.createdAt >= :date')
            ->setParameter('date', $date)
            ->orderBy('u.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Находит пользователей, зарегистрированных до указанной даты
     */
    public function findRegisteredBefore(\DateTimeInterface $date): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.createdAt <= :date')
            ->setParameter('date', $date)
            ->orderBy('u.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Получает общее количество пользователей
     */
    public function countAll(): int
    {
        return $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Находит пользователей с pagination
     */
    public function findPaginated(int $page = 1, int $limit = 10): array
    {
        return $this->createQueryBuilder('u')
            ->orderBy('u.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
