<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use App\Service\ActivityLoggerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/category/admin')]
#[IsGranted('ROLE_ADMIN')]
final class CategoryAdminController extends AbstractController
{
    public function __construct(
        private ActivityLoggerService $activityLogger,
    ) {}
    #[Route(name: 'app_category_admin_index', methods: ['GET'])]
    public function index(CategoryRepository $categoryRepository): Response
    {
        return $this->render('admin/category_admin/index.html.twig', [
            'categories' => $categoryRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_category_admin_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($category);
            $entityManager->flush();

            // Log the activity
            $user = $this->getUser();
            if ($user instanceof \App\Entity\User) {
                $this->activityLogger->logActivity(
                    $user,
                    'CREATE',
                    "Category: {$category->getName()} (ID: {$category->getId()})"
                );
            }

            return $this->redirectToRoute('app_category_admin_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/category_admin/new.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_category_admin_show', methods: ['GET'])]
    public function show(Category $category): Response
    {
        return $this->render('admin/category_admin/show.html.twig', [
            'category' => $category,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_category_admin_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            // Log the activity
            $user = $this->getUser();
            if ($user instanceof \App\Entity\User) {
                $this->activityLogger->logActivity(
                    $user,
                    'UPDATE',
                    "Category: {$category->getName()} (ID: {$category->getId()})"
                );
            }

            return $this->redirectToRoute('app_catery_admin_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/category_admin/edit.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_category_admin_delete', methods: ['POST'])]
    public function delete(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$category->getId(), $request->getPayload()->getString('_token'))) {
            try {
                // Log the activity before deletion
                $user = $this->getUser();
                if ($user instanceof \App\Entity\User) {
                    $this->activityLogger->logActivity(
                        $user,
                        'DELETE',
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
        
        return $this->redirectToRoute('app_category_admin_index', [], Response::HTTP_SEE_OTHER);
    }
}
