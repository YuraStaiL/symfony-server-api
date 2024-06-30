<?php

namespace App\Service;

use App\Entity\UserGroup;
use App\Repository\UserGroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class UserGroupService
{
    private EntityManagerInterface $entityManager;
    private ?EntityRepository $repository;

    public function __construct(
        EntityManagerInterface $entityManager,
        EntityRepository $repository = null
    ) {
        $this->entityManager    = $entityManager;
        $this->repository       = $repository;
    }

    public function create(UserGroup $group): UserGroup
    {
        $this->entityManager->persist($group);
        $this->entityManager->flush();

        return $group;
    }

    public function update(UserGroup $group): UserGroup
    {
        $this->entityManager->flush();

        return $group;
    }

    public function delete(UserGroup $group): void
    {
        $this->entityManager->remove($group);
        $this->entityManager->flush();
    }

    public function findById(int $id): ?UserGroup
    {
        return $this->repository->find($id);
    }

    public function countUsers(int $id)
    {
        return $this->repository->countUsers($id);
    }

    public function getGroupUsers(): array
    {
        $groups = $this->repository->findAll();
        $groupUsersMap = [];
        foreach ($groups as $group) {
            $groupData = $group->toArray();
            $groupData['users'] = [];
            foreach ($group->getUsers() as $user) {
                $groupData['users'][] = $user->toArray();
            }

            $groupUsersMap[] = $groupData ?? [];
        }

        return $groupUsersMap;
    }
}
