<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements UserLoaderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * I specifically did not call findOnyByUsername because you might want to load by email
     * or other identifier later. In which case, both methods are required.
     */
    public function loadUserByIdentifier(string $identifier): ?UserInterface
    {
        return $this->createQueryBuilder('u')
            ->where('LOWER(u.username) = LOWER(:query)')
            ->setParameter('query', $identifier)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findOneByUsername(string $username): ?User
    {
        return $this->createQueryBuilder('u')
            ->where('LOWER(u.username) = LOWER(:query)')
            ->setParameter('query', $username)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findOneByEmail(string $email): ?User
    {
        return $this->createQueryBuilder('u')
            ->where('LOWER(u.email) = LOWER(:query)')
            ->setParameter('query', $email)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
