<?php

namespace App\Controller;

use App\Entity\Ingredient;
use App\Entity\User;
use App\Form\IngredientType;
use App\Repository\IngredientRepository;
use App\Service\ActivityLoggerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/staff/ingredient')]
#[IsGranted('ROLE_STAFF')]
final class StaffIngredientController extends AbstractController
{
    public function __construct(
        private ActivityLoggerService $activityLogger,
    ) {}
    #[Route(name: 'app_staff_ingredient_index', methods: ['GET'])]
    public function index(IngredientRepository $ingredientRepository): Response
    {
        $ingredients = $ingredientRepository->findAll();
        
        // Group ingredients by type
        $ingredientsByType = [
            'Size' => [],
            'Base' => [],
            'Cheese' => [],
            'Topping' => [],
        ];
        
        foreach ($ingredients as $ingredient) {
            $type = $ingredient->getType();
            if (isset($ingredientsByType[$type])) {
                $ingredientsByType[$type][] = $ingredient;
            }
        }
        
        return $this->render('staff/ingredient/index.html.twig', [
            'ingredientsByType' => $ingredientsByType,
        ]);
    }

    #[Route('/new', name: 'app_staff_ingredient_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $ingredient = new Ingredient();
        $form = $this->createForm(IngredientType::class, $ingredient);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ingredient->setCreatedBy($this->getUser());
            $entityManager->persist($ingredient);
            $entityManager->flush();

            // Log the activity
            $user = $this->getUser();
            if ($user instanceof User) {
                $this->activityLogger->logActivity(
                    $user,
                    'CREATE',
                    "Ingredient: {$ingredient->getName()} (ID: {$ingredient->getId()})"
                );
            }

            return $this->redirectToRoute('app_staff_ingredient_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('staff/ingredient/new.html.twig', [
            'ingredient' => $ingredient,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_staff_ingredient_show', methods: ['GET'])]
    public function show(Ingredient $ingredient): Response
    {
        return $this->render('staff/ingredient/show.html.twig', [
            'ingredient' => $ingredient,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_staff_ingredient_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Ingredient $ingredient, EntityManagerInterface $entityManager): Response
    {
        if ($ingredient->getCreatedBy() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You can only edit ingredients you created.');
        }

        $form = $this->createForm(IngredientType::class, $ingredient);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            // Log the activity
            $user = $this->getUser();
            if ($user instanceof User) {
                $this->activityLogger->logActivity(
                    $user,
                    'UPDATE',
                    "Ingredient: {$ingredient->getName()} (ID: {$ingredient->getId()})"
                );
            }

            return $this->redirectToRoute('app_staff_ingredient_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('staff/ingredient/edit.html.twig', [
            'ingredient' => $ingredient,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_staff_ingredient_delete', methods: ['POST'])]
    public function delete(Request $request, Ingredient $ingredient, EntityManagerInterface $entityManager): Response
    {
        if ($ingredient->getCreatedBy() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You can only delete ingredients you created.');
        }

        if ($this->isCsrfTokenValid('delete'.$ingredient->getId(), $request->getPayload()->getString('_token'))) {
            // Log the activity before deletion
            $user = $this->getUser();
            if ($user instanceof User) {
                $this->activityLogger->logActivity(
                    $user,
                    'DELETE',
                    "Ingredient: {$ingredient->getName()} (ID: {$ingredient->getId()})"
                );
            }

            $entityManager->remove($ingredient);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_staff_ingredient_index', [], Response::HTTP_SEE_OTHER);
    }
}
