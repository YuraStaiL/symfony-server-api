<?php

namespace App\Validator;

use App\Entity\User;
use App\Entity\UserGroup;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class UserValidator
{
    public function __construct(
        private ?string                 $name,
        private ?string                 $email,
        private ?int                    $userGroupId,
        private ?UserGroup              $group,
        private ValidatorInterface      $validator
    ) {
    }

    public function validate(): ?array
    {
        $errors = [];

        if ($this->userGroupId && !$this->group) {
            $errors['userGroup'] = "not found with id $this->userGroupId";

            return [
                'errors'    => $errors,
                'code'      => Response::HTTP_NOT_FOUND
            ];
        }

        $user = new User();
        $user->setName($this->name)
            ->setEmail($this->email)
            ->setGroup($this->group ?? null);

        $errors += PrintValidatorError::handle($this->validator, $user);

        if ($errors) {
            return [
                'errors'    => $errors,
                'code'      => Response::HTTP_BAD_REQUEST
            ];
        }

        return null;
    }
}