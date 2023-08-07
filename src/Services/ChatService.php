<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Chat;
use Doctrine\ORM\EntityManagerInterface;

class ChatService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function create(string $chatName): Chat
    {
        $chat = new Chat($chatName);

        $this->entityManager->persist($chat);
        $this->entityManager->flush();

        return $chat;
    }
}
