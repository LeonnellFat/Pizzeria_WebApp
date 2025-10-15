<?php

namespace App\Controller;

use App\Entity\Pizza;
use App\Form\PizzaType;
use App\Repository\PizzaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/pizza/admin')]
final class PizzaAdminController extends AbstractController
{
    #[Route(name: 'app_pizza_admin_index', methods: ['GET'])]
    public function index(PizzaRepository $pizzaRepository): Response
    {
        return $this->render('pizza_admin/index.html.twig', [
            'pizzas' => $pizzaRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_pizza_admin_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $pizza = new Pizza();
        $form = $this->createForm(PizzaType::class, $pizza);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($pizza);
            $entityManager->flush();

            return $this->redirectToRoute('app_pizza_admin_index');
        }

        return $this->render('pizza_admin/new.html.twig', [
            'pizza' => $pizza,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_pizza_admin_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Pizza $pizza, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PizzaType::class, $pizza);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_pizza_admin_index');
        }

        return $this->render('pizza_admin/edit.html.twig', [
            'pizza' => $pizza,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_pizza_admin_delete', methods: ['POST'])]
    public function delete(Request $request, Pizza $pizza, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$pizza->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($pizza);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_pizza_admin_index');
    }
}
