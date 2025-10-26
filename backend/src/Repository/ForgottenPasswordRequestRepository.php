<?php

namespace App\Repository;

use App\Entity\ForgottenPasswordRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ForgottenPasswordRequest>
 */
class ForgottenPasswordRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ForgottenPasswordRequest::class);
    }

    public function findOneByCode(string $code): ?ForgottenPasswordRequest
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.code = :code')
            ->andWhere('f.expiresAt > :now')
            ->setParameter('code', $code)
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
