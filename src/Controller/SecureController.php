<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SecureController extends AbstractController
{
    #[Route('/', name: 'app_secure')]
    public function index(): Response
    {
        return $this->render('secure/welcome.html.twig');
    }
}
