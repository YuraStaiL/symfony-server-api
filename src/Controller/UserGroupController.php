<?php

namespace App\Controller;

use App\Entity\UserGroup;
use App\Repository\UserGroupRepository;
use App\Validator\PrintValidatorError;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\UserGroupService;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserGroupController extends AbstractController
{
    #[Route('/api/groups/users-list', methods: ['GET', 'HEAD'])]
    public function showUsers(
        EntityManagerInterface $entityManager,
        ManagerRegistry $managerRegistry,
    ): Response {
        $repository = new UserGroupRepository($managerRegistry);
        $service = new UserGroupService($entityManager, $repository);

        $groupUsers = $service->getGroupUsers();

        return new JsonResponse(
            $groupUsers
            , Response::HTTP_OK);
    }

    #[Route('/api/groups/{id}', methods: ['GET', 'HEAD'])]
    public function show(
        EntityManagerInterface $entityManager,
        ManagerRegistry $managerRegistry,
        int $id
    ): Response {
        $repository = new UserGroupRepository($managerRegistry);
        $service = new UserGroupService($entityManager, $repository);

        $group = $service->findById($id);
        if (!$group) {
            return new JsonResponse([
                'errors' => [
                    'group' => "Not found with id $id"
                ],
            ], Response::HTTP_NOT_FOUND);
        }

        $response   = $group->toArray();

        return new JsonResponse(
            $response
            , Response::HTTP_OK);
    }

    #[Route('/api/groups', methods: ['POST'])]
    public function create(
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): Response {
        $request    = Request::createFromGlobals();
        $name       = $request->getPayload()->get('name');

        $service    = new UserGroupService($entityManager, $entityManager->getRepository(UserGroup::class));
        $group      = (new UserGroup())->setName($name);
        $errors     = PrintValidatorError::handle($validator, $group);

        if ($errors) {
            return new JsonResponse(
                ['errors' => $errors],
                Response::HTTP_BAD_REQUEST
            );
        }

        $group      = $service->create($group);
        $response   = $group->toArray();

        return new JsonResponse(
            $response
        , Response::HTTP_CREATED);
    }

    #[Route('/api/groups/{id}', methods: ['PUT', 'PATCH'])]
    public function edit(
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        int $id
    ): Response {
        $service = new UserGroupService($entityManager, $entityManager->getRepository(UserGroup::class));

        $group = $service->findById($id);
        if (!$group) {
            return new JsonResponse([
                'errors' => [
                    'group' => "Not found with id $id"
                ],
            ], Response::HTTP_NOT_FOUND);
        }

        $request    = Request::createFromGlobals();
        $name       = $request->getPayload()->get('name');
        $group->setName($name);

        $errors     = PrintValidatorError::handle($validator, $group);
        if ($errors) {
            return new JsonResponse(
                ['errors' => $errors],
                Response::HTTP_BAD_REQUEST
            );
        }

        $group      = $service->update($group);
        $response   = $group->toArray();

        return new JsonResponse(
            $response
            , Response::HTTP_OK);
    }

    #[Route('/api/groups/{id}', methods: ['DELETE'])]
    public function delete(
        EntityManagerInterface $entityManager,
        int $id
    ): JsonResponse {
        $service = new UserGroupService($entityManager, $entityManager->getRepository(UserGroup::class));

        $group = $service->findById($id);
        if (!$group) {
            return new JsonResponse([
                'errors' => [
                    'group' => "Not found with id $id"
                ],
            ], Response::HTTP_NOT_FOUND);
        } else if ($service->countUsers($id)) {
            return new JsonResponse([
                'errors' => [
                    'group' => "Have users"
                ],
            ], Response::HTTP_CONFLICT);
        }

        $service->delete($group);

        return new JsonResponse(
            null,
            Response::HTTP_NO_CONTENT
        );
    }
}