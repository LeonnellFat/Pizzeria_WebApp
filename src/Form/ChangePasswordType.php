<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('currentPassword', PasswordType::class, [
                'label' => 'Current Password',
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter your current password.',
                    ]),
                ],
                'attr' => [
                    'class' => 'w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-[#D62828]',
                    'placeholder' => 'Enter current password',
                    'autocomplete' => 'current-password'
                ]
            ])
            ->add('newPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'first_options' => [
                    'label' => 'New Password',
                    'constraints' => [
                        new NotBlank([
                            'message' => 'New password is required.',
                        ]),
                        new Length([
                            'min' => 6,
                            'minMessage' => 'Password must be at least 6 characters long.',
                            'max' => 4096,
                        ]),
                    ],
                    'attr' => [
                        'class' => 'w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-[#D62828]',
                        'placeholder' => 'Enter new password',
                        'autocomplete' => 'new-password'
                    ]
                ],
                'second_options' => [
                    'label' => 'Confirm New Password',
                    'attr' => [
                        'class' => 'w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-[#D62828]',
                        'placeholder' => 'Confirm new password',
                        'autocomplete' => 'new-password'
                    ]
                ],
                'invalid_message' => 'The passwords do not match. Please try again.',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
