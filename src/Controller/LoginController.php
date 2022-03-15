<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    #[Route('/', name: 'login')]
    public function index(AuthenticationUtils $authenticationUtils): Response
    {
        
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        if($this->getUser()){
            return $this->redirectToRoute("cliente");
        }else{
            return $this->render('login/index.html.twig', [
                    'controller_name' => 'LoginController',
                    'last_username' => $lastUsername,
                    'error' => $error,
        ]);
        }
    }
}
