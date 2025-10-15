<?php

namespace App\Controller;

use App\Repository\PizzaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class PizzasController extends AbstractController
{
    #[Route('/pizzas', name: 'app_pizzas')]
    public function index(PizzaRepository $pizzaRepository): Response
    {
        // Fetch pizzas from database (only available ones)
        $pizzas = $pizzaRepository->findBy(['isAvailable' => true]);

        // Pass the pizzas variable to the template
        return $this->render('pizzas/index.html.twig', [
            'pizzas' => $pizzas,
        ]);
    }
}
