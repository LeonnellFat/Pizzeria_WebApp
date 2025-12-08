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
use Symfony\Component\HttpFoundation\File\UploadedFile;

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
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('image')->getData();
            
            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                $imageFile->move(
                    $this->getParameter('pizzas_directory'),
                    $newFilename
                );
                $pizza->setImage($newFilename);
            }

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
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('image')->getData();
            
            if ($imageFile) {
                // Delete old image if it exists
                if ($pizza->getImage()) {
                    $oldImagePath = $this->getParameter('pizzas_directory') . '/' . $pizza->getImage();
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }

                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                $imageFile->move(
                    $this->getParameter('pizzas_directory'),
                    $newFilename
                );
                $pizza->setImage($newFilename);
            }

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
