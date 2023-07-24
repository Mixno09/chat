<?php

declare(strict_types=1);

namespace App\Form;

use App\Validator\User\UniqueEmail;
use Symfony\Component\Validator\Constraints as Assert;

class RegistrationData
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 180)]
    #[Assert\Email]
    #[UniqueEmail]
    public ?string $email = null;
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    public ?string $password = null;
    #[Assert\NotBlank]
    #[Assert\IdenticalTo(propertyPath: 'password')]
    public ?string $repeatPassword = null;
}
