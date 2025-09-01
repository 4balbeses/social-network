<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function findAll(): array
    {
        return $this->createQueryBuilder('u')
            ->select('u', 'p', 't')
            ->leftJoin('u.playlists', 'p')
            ->leftJoin('u.tags', 't')
            ->orderBy('u.registeredAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOneById(int $id): ?User
    {
        return $this->createQueryBuilder('u')
            ->select('u', 'p', 't')
            ->leftJoin('u.playlists', 'p')
            ->leftJoin('u.tags', 't')
            ->where('u.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByUsername(string $username): ?User
    {
        return $this->createQueryBuilder('u')
            ->select('u')
            ->where('u.username = :username')
            ->setParameter('username', $username)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findActiveUsersWithPlaylists(): array
    {
        return $this->createQueryBuilder('u')
            ->select('u', 'p')
            ->leftJoin('u.playlists', 'p')
            ->where('p.isPublic = :isPublic')
            ->setParameter('isPublic', true)
            ->orderBy('u.registeredAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}