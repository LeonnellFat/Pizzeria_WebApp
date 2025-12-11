<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordType;
use App\Form\EditUsernameType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/myprofile')]
#[IsGranted('ROLE_ADMIN')]
final class MyProfileAdminController extends AbstractController
{
    #[Route('', name: 'app_myprofile_admin', methods: ['GET'])]
    public function index(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('admin/myprofile_admin/index.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/edit-username', name: 'app_myprofile_edit_username', methods: ['GET', 'POST'])]
    public function editUsername(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(EditUsernameType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $user->setUpdatedAt(new \DateTimeImmutable());
                $entityManager->flush();

                $this->addFlash('success', 'Username updated successfully!');
                return $this->redirectToRoute('app_myprofile_admin');
            } catch (\Exception $e) {
                $this->addFlash('error', 'An error occurred while updating your username: ' . $e->getMessage());
            }
        }

        return $this->render('admin/myprofile_admin/edit_username.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/change-password', name: 'app_myprofile_change_password', methods: ['GET', 'POST'])]
    public function changePassword(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $currentPassword = $form->get('currentPassword')->getData();
                $newPassword = $form->get('newPassword')->getData();

                // Verify current password
                if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                    $this->addFlash('error', 'Current password is incorrect.');
                    return $this->render('admin/myprofile_admin/change_password.html.twig', [
                        'form' => $form,
                    ]);
                }

                // Hash and set new password
                $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
                $user->setPassword($hashedPassword);
                $user->setUpdatedAt(new \DateTimeImmutable());

                $entityManager->flush();

                $this->addFlash('success', 'Password changed successfully!');
                return $this->redirectToRoute('app_myprofile_admin');
            } catch (\Exception $e) {
                $this->addFlash('error', 'An error occurred while changing your password: ' . $e->getMessage());
            }
        }

        return $this->render('admin/myprofile_admin/change_password.html.twig', [
            'form' => $form,
        ]);
    }
}
