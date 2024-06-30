<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserGroup;
use App\Repository\UserRepository;
use App\Service\UserGroupService;
use App\Service\UserService;
use App\Validator\PrintValidatorError;
use App\Validator\UserValidator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserController extends AbstractController
{
    #[Route('/api/users/{id}', methods: ['GET', 'HEAD'])]
    public function show(
        EntityManagerInterface $entityManager,
        ManagerRegistry $managerRegistry,
        int $id
    ): Response {
        $repository = new UserRepository($managerRegistry);
        $service = new UserService($entityManager, $repository);

        $user = $service->findById($id);
        if (!$user) {
            return new JsonResponse([
                'error' => 'User not found',
            ], Response::HTTP_NOT_FOUND);
        }

        $response   = $user->toArray();

        return new JsonResponse(
            $response
            , Response::HTTP_OK);
    }

    #[Route('/api/users', methods: ['POST'])]
    public function create(
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
    ): Response {
        $request    = Request::createFromGlobals();
        $service    = new UserService($entityManager, $entityManager->getRepository(User::class));
        $errors     = [];

        $name       = $request->getPayload()->get('name');
        $email      = $request->getPayload()->get('email');
        $userGroup   = $request->getPayload()->get('userGroup');

        if ($userGroup) {
            $groupRepository    = $entityManager->getRepository(UserGroup::class);
            $groupService       = new UserGroupService($entityManager, $groupRepository);
            $group              = $groupService->findById($userGroup);
            if (!$group) {
                $errors['userGroup'] = "not found with id $userGroup";
                return new JsonResponse(
                    ['errors' => $errors],
                    Response::HTTP_NOT_FOUND
                );
            }
        }

        $user = new User();
        $user->setName($name);
        $user->setEmail($email);
        $user->setGroup($group ?? null);

        $errors += PrintValidatorError::handle($validator, $user);

        if ($errors) {
            return new JsonResponse(
                ['errors' => $errors],
                400
            );
        }

        $user       = $service->create($user);
        $response   = $user->toArray();

        return new JsonResponse(
            $response,
            Response::HTTP_CREATED);
    }

    #[Route('/api/users/{id}', methods: ['PUT', 'PATCH'])]
    public function edit(
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        int $id
    ): Response {
        $request    = Request::createFromGlobals();
        $service    = new UserService($entityManager, $entityManager->getRepository(User::class));
        $errors     = [];

        $user = $service->findById($id);
        if (!$user) {
            $errors['user'] = "Not found with id $id";
            return new JsonResponse(
                ['errors' => $errors],
                Response::HTTP_NOT_FOUND
            );
        }

        $name               = $request->getPayload()->get('name');
        $email              = $request->getPayload()->get('email');
        $userGroup          = $request->getPayload()->get('userGroup');

        if ($request->getMethod() === "PATCH") {
            $name           = $name ?? $user->getName();
            $email          = $email ?? $user->getEmail();
            $userGroup      = $userGroup ?? $user->getGroup()->getId();
        }
        $userGroup = (int) $userGroup;

        if ($userGroup) {
            $groupRepository    = $entityManager->getRepository(UserGroup::class);
            $groupService       = new UserGroupService($entityManager, $groupRepository);
            $group              = $groupService->findById($userGroup);
        }

        $userValidator = new UserValidator($name, $email, $userGroup, $group ?? null, $validator);
        $errors = $userValidator->validate();

        if ($errors) {
            return new JsonResponse(
                ['errors' => $errors['errors']],
                $errors['code']
            );
        }

        $user->setName($name);
        $user->setEmail($email);
        $user->setGroup($group);

        $user       = $service->update($user);
        $response   = $user->toArray();

        return new JsonResponse(
            $response
            , Response::HTTP_OK);
    }

    #[Route('/api/users/{id}', methods: ['DELETE'])]
    public function delete(
        EntityManagerInterface $entityManager,
        int $id
    ): JsonResponse {
        $service = new UserService($entityManager, $entityManager->getRepository(User::class));

        $user = $service->findById($id);
        if (!$user) {
            return new JsonResponse([
                'errors' => [
                    'user' => "Not found with id $id"
                ],
            ], Response::HTTP_NOT_FOUND);
        }

        $service->delete($user);

        return new JsonResponse(
            null,
            Response::HTTP_NO_CONTENT
        );
    }
}