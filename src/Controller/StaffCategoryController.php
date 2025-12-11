<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\User;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use App\Service\ActivityLoggerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/staff/category')]
#[IsGranted('ROLE_STAFF')]
final class StaffCategoryController extends AbstractController
{
    public function __construct(
        private ActivityLoggerService $activityLogger,
    ) {}
    #[Route(name: 'app_staff_category_index', methods: ['GET'])]
    public function index(CategoryRepository $categoryRepository): Response
    {
        return $this->render('staff/category/index.html.twig', [
            'categories' => $categoryRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_staff_category_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category->setCreatedBy($this->getUser());
            $entityManager->persist($category);
            $entityManager->flush();

            // Log the activity
            $user = $this->getUser();
            if ($user instanceof User) {
                $this->activityLogger->logActivity(
                    $user,
                    'CREATE',
                    "Category: {$category->getName()} (ID: {$category->getId()})"
                );
            }

            return $this->redirectToRoute('app_staff_category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('staff/category/new.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_staff_category_show', methods: ['GET'])]
    public function show(Category $category): Response
    {
        return $this->render('staff/category/show.html.twig', [
            'category' => $category,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_staff_category_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        if ($category->getCreatedBy() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You can only edit categories you created.');
        }

        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            // Log the activity
            $user = $this->getUser();
            if ($user instanceof User) {
                $this->activityLogger->logActivity(
                    $user,
                    'UPDATE',
                    "Category: {$category->getName()} (ID: {$category->getId()})"
                );
            }

            return $this->redirectToRoute('app_staff_category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('staff/category/edit.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_staff_category_delete', methods: ['POST'])]
    public function delete(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        if ($category->getCreatedBy() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You can only delete categories you created.');
        }

        if ($this->isCsrfTokenValid('delete'.$category->getId(), $request->getPayload()->getString('_token'))) {
            try {
                // Log the activity before deletion
                $user = $this->getUser();
                if ($user instanceof User) {
                    $this->activityLogger->logActivity(
                        $user,
                        'DELETE_CATEGORY',
                        "Category: {$category->getName()} (ID: {$category->getId()})"
                    );
                }

                $entityManager->remove($category);
                $entityManager->flush();
                $this->addFlash('success', 'Category deleted successfully!');
            } catch (\Exception $e) {
                // Check if it's a foreign key constraint violation
                if (strpos($e->getMessage(), 'SQLSTATE[23000]') !== false || strpos($e->getMessage(), 'foreign key') !== false) {
                    $this->addFlash('error', 'Unable to delete category. It is currently assigned to existing products.');
                } else {
                    $this->addFlash('error', 'An error occurred while deleting the category.');
                }
            }
        }
        
        return $this->redirectToRoute('app_staff_category_index', [], Response::HTTP_SEE_OTHER);
    }
}
