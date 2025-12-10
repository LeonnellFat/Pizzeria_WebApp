<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ActivityLoggerController extends AbstractController
{
    #[Route('/activity/logger', name: 'app_activity_logger')]
    public function index(): Response
    {
        return $this->render('activity_logger/index.html.twig', [
            'controller_name' => 'ActivityLoggerController',
        ]);
    }
}
