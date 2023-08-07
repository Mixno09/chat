<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\User;
use App\Exception\EntityExistsException;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    private UserRepository $userRepository;
    private UserPasswordHasherInterface $passwordHasher;
    private EntityManagerInterface $entityManager;

    public function __construct(
        UserRepository              $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface      $entityManager
    ) {
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
        $this->entityManager = $entityManager;
    }

    public function create(string $email, string $password): User
    {
        $user = $this->userRepository->getUserByEmail($email);
        if ($user instanceof User) {
            throw new EntityExistsException("User by email {$email} already exist.");
        }

        $user = new User($email);
        $passwordHash = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($passwordHash);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}
