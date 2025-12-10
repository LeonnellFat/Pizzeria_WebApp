<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/user/management')]
#[IsGranted('ROLE_ADMIN')]
final class UserManagementController extends AbstractController
{
    #[Route(name: 'app_user_management_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user_management/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_user_management_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Get the password from the form (since it's mapped: false)
                $plainPassword = $form->get('password')->getData();
                
                // Validate password is not empty
                if (empty($plainPassword)) {
                    $this->addFlash('error', 'Password cannot be empty.');
                    return $this->render('user_management/new.html.twig', [
                        'user' => $user,
                        'form' => $form,
                    ]);
                }

                // Hash and set the password
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);

                // Set default values
                $user->setCreatedAt(new \DateTimeImmutable());
                $user->setIsActive($form->get('isActive')->getData() ?? true);

                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'User account created successfully!');
                return $this->redirectToRoute('app_user_management_index', [], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $this->addFlash('error', 'An error occurred while creating the user: ' . $e->getMessage());
            }
        }

        return $this->render('user_management/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_management_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user_management/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_management_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Get the password from the form
                $plainPassword = $form->get('password')->getData();
                
                // Hash and set the password
                if ($plainPassword) {
                    $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                    $user->setPassword($hashedPassword);
                }

                // Update the modification timestamp
                $user->setUpdatedAt(new \DateTimeImmutable());

                $entityManager->flush();

                $this->addFlash('success', 'User updated successfully!');
                return $this->redirectToRoute('app_user_management_index', [], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $this->addFlash('error', 'An error occurred while updating the user: ' . $e->getMessage());
            }
        }

        return $this->render('user_management/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_management_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_management_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/deactivate', name: 'app_user_management_deactivate', methods: ['POST'])]
    public function deactivate(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('deactivate'.$user->getId(), $request->getPayload()->getString('_token'))) {
            $user->setIsActive(false);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_management_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/activate', name: 'app_user_management_activate', methods: ['POST'])]
    public function activate(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('activate'.$user->getId(), $request->getPayload()->getString('_token'))) {
            $user->setIsActive(true);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_management_index', [], Response::HTTP_SEE_OTHER);
    }
}
