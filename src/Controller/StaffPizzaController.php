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
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[Route('/staff/pizza')]
#[IsGranted('ROLE_STAFF')]
final class StaffPizzaController extends AbstractController
{
    #[Route(name: 'app_staff_pizza_index', methods: ['GET'])]
    public function index(PizzaRepository $pizzaRepository): Response
    {
        return $this->render('staff_pizza/index.html.twig', [
            'pizzas' => $pizzaRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_staff_pizza_new', methods: ['GET', 'POST'])]
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

            $pizza->setCreatedBy($this->getUser());
            $entityManager->persist($pizza);
            $entityManager->flush();

            return $this->redirectToRoute('app_staff_pizza_index');
        }

        return $this->render('staff_pizza/new.html.twig', [
            'pizza' => $pizza,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_staff_pizza_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Pizza $pizza, EntityManagerInterface $entityManager): Response
    {
        if ($pizza->getCreatedBy() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You can only edit pizzas you created.');
        }

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

            return $this->redirectToRoute('app_staff_pizza_index');
        }

        return $this->render('staff_pizza/edit.html.twig', [
            'pizza' => $pizza,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_staff_pizza_delete', methods: ['POST'])]
    public function delete(Request $request, Pizza $pizza, EntityManagerInterface $entityManager): Response
    {
        if ($pizza->getCreatedBy() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You can only delete pizzas you created.');
        }

        if ($this->isCsrfTokenValid('delete'.$pizza->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($pizza);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_staff_pizza_index');
    }
}
