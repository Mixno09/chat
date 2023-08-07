<?php

namespace App\Controller;

use App\Form\ChatData;
use App\Form\ChatType;
use App\Form\MessageData;
use App\Form\MessageType;
use App\Form\SearchMessageData;
use App\Form\SearchMessageType;
use App\Repository\ChatRepository;
use App\Repository\MessageRepository;
use App\Services\ChatService;
use App\Services\MessageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChatController extends AbstractController
{
    private ChatRepository $chatRepository;
    private ChatService $chatService;
    private MessageService $messageService;
    private MessageRepository $messageRepository;

    public function __construct(ChatRepository $chatRepository, ChatService $chatService, MessageService $messageService, \App\Repository\MessageRepository $messageRepository)
    {
        $this->chatRepository = $chatRepository;
        $this->chatService = $chatService;
        $this->messageService = $messageService;
        $this->messageRepository = $messageRepository;
    }

    #[Route('/{chatId}', name: 'chat', requirements: ['chatId' => '\d+'])]
    public function indexChatAction(Request $request, ?int $chatId = null): Response
    {
        $chat = null;
        if ($chatId !== null) {
            $chat = $this->chatRepository->getChatByChatId($chatId);
            if ($chat === null) {
                throw $this->createNotFoundException();
            }
        }

        $chats = $this->chatRepository->getAllChats();

        $formCreateChat = $this->createForm(ChatType::class, options: [
            'action' => $this->generateUrl('create_chat'),
        ]);

        $formCreateMessage = null;
        if ($chatId !== null) {
            $formCreateMessage = $this->createForm(MessageType::class, options: [
                'action' => $this->generateUrl('create_message', ['chatId' => $chatId])
            ]);
        }

        $search = null;
        if ($chatId !== null) {
            $search = $request->query->get('q');
            if (is_string($search)) {
                $search = trim($search);
            }
            if (! is_string($search) || $search === '') {
                $search = null;
            }
        }

        $messages = [];
        if ($chatId !== null) {
            $messages = $this->messageRepository->getAllMessageByChatId($chatId, $search);
        }

        $errors = $request->getSession()->getFlashBag()->get('chat_errors');

        return $this->render('chat/chat.html.twig', [
            'chatId' => $chatId,
            'chats' => $chats,
            'messages' => $messages,
            'formCreateChat' => $formCreateChat,
            'formCreateMessage' => $formCreateMessage,
            'errors' => $errors,
            'search' => $search,
        ]);
    }

    #[Route('/create-chat', name: 'create_chat', methods: ['GET', 'POST'])]
    public function createChatAction(Request $request): Response
    {
        $data = new ChatData();
        $form = $this->createForm(ChatType::class, $data);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $chat = $this->chatService->create($data->name);
            return $this->redirectToRoute('chat', ['chatId' => $chat->getId()]);
        }

        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            /** @var \Symfony\Component\Form\FormError $error */
            $errors[] = $error->getMessage();
        }
        $this->addFlash('chat_errors', $errors);

        return $this->redirectToRoute('chat');
    }

    #[Route('/{chatId}/create-message', name: 'create_message', requirements: ['chatId' => '\d+'], methods: 'POST')]
    public function createMessageAction(Request $request, int $chatId): Response
    {
        $chat = $this->chatRepository->getChatByChatId($chatId);
        if ($chat === null) {
            throw $this->createNotFoundException();
        }

        $data = new MessageData();
        $form = $this->createForm(MessageType::class, $data);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->messageService->create(
                $data->text,
                $chat,
                $this->getUser(),
            );
        } else {
            $errors = [];
            foreach ($form->getErrors(true) as $error) {
                /** @var \Symfony\Component\Form\FormError $error */
                $errors[] = $error->getMessage();
            }
            $this->addFlash('chat_errors', $errors);
        }

        return $this->redirectToRoute('chat', ['chatId' => $chat->getId()]);
    }
}
