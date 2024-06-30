<?php

namespace App\Validator;

use Symfony\Component\Validator\Validator\ValidatorInterface;

class PrintValidatorError
{
    public static function handle(
        ValidatorInterface $validator,
        object $entity
    ): ?array {
        $errors = $validator->validate($entity);

        foreach ($errors as $error) {
            $jsonErrors[$error->getPropertyPath()] = $error->getMessage();
        }

        return $jsonErrors ?? [];
    }
}