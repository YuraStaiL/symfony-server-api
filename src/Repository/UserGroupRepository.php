<?php

namespace App\Repository;

use App\Entity\UserGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserGroup>
 */
class UserGroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserGroup::class);
    }

    public function countUsers(int $id): int
    {
        return $this->createQueryBuilder('g')
            ->select('COUNT(u.id)')
            ->leftJoin('g.users', 'u')
            ->where('u.userGroup = :groupId')
            ->setParameter('groupId', $id)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
