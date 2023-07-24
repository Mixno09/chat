<?php

namespace App\Validator\User;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class UniqueEmail extends Constraint
{
    public string $message = 'Пользователь с email {{ email }} уже существует.';
}
