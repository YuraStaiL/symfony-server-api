<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class UserService
{
    private EntityManagerInterface $entityManager;
    private ?EntityRepository $repository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ?EntityRepository $repository = null
    ) {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
    }

    public function create(
        User $user
    ): User {
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function update(
        User $user
    ): User {
        $this->entityManager->flush();

        return $user;
    }

    public function delete(User $user): void {
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }

    public function findById(int $id): ?User {
        return $this->repository->find($id);
    }
}
