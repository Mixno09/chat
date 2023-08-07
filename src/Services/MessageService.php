<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Chat;
use App\Entity\Message;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class MessageService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function create(string $text, Chat $chat, User $user): Message
    {
        $message = new Message($text, $chat, $user);

        $this->entityManager->persist($message);
        $this->entityManager->flush();

        return $message;
    }
}
