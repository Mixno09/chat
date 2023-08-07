<?php

namespace App\Controller;

use App\Form\RegistrationData;
use App\Form\RegistrationType;
use App\Services\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    private UserService $userService;
    private Security $security;

    public function __construct(UserService $userService, Security $security)
    {
        $this->userService = $userService;
        $this->security = $security;
    }

    #[Route('/registration', name: 'security_registration', methods: ['GET', 'POST'])]
    public function registrationAction(Request $request): Response
    {
        $data = new RegistrationData();
        $form = $this->createForm(RegistrationType::class, $data);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->userService->create($data->email, $data->password);
            $response = $this->security->login($user, 'form_login');
            if ($response instanceof Response) {
                return $response;
            }
            return $this->redirectToRoute('/');
        }
        return $this->render('security/registration.html.twig', ['form' =>  $form]);
    }

    #[Route('/login', name: 'security_login', methods: ['GET', 'POST'])]
    public function loginAction(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('chat');
        }
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUserEmail = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'error' => $error,
            'last_user_email' => $lastUserEmail,
        ]);
    }

    #[Route('/logout', name: 'security_logout', methods: ['POST'])]
    public function logoutAction(): never
    {
        // controller can be blank: it will never be called!
        throw new \Exception('Don\'t forget to activate logout in security.yaml');
    }
}
