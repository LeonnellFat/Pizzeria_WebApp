<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', null, [
                'label' => 'Username',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Username is required.',
                    ]),
                    new Length([
                        'min' => 3,
                        'minMessage' => 'Username must be at least 3 characters long.',
                        'max' => 180,
                        'maxMessage' => 'Username cannot be longer than 180 characters.',
                    ]),
                ],
                'attr' => [
                    'class' => 'w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-[#D62828]',
                    'placeholder' => 'Enter username'
                ]
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'Roles',
                'choices' => [
                    'Admin' => 'ROLE_ADMIN',
                    'Staff' => 'ROLE_STAFF',
                ],
                'multiple' => true,
                'expanded' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please select at least one role.',
                    ]),
                ],
                'attr' => [
                    'class' => 'w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-[#D62828]',
                    'size' => 2,
                ]
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'Password',
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Password is required.',
                        ]),
                        new Length([
                            'min' => 6,
                            'minMessage' => 'Password must be at least 6 characters long.',
                            'max' => 4096,
                        ]),
                    ],
                    'attr' => [
                        'class' => 'w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-[#D62828]',
                        'placeholder' => 'Enter password',
                        'autocomplete' => 'new-password'
                    ]
                ],
                'second_options' => [
                    'label' => 'Confirm Password',
                    'attr' => [
                        'class' => 'w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-[#D62828]',
                        'placeholder' => 'Confirm password',
                        'autocomplete' => 'new-password'
                    ]
                ],
                'invalid_message' => 'The passwords do not match. Please try again.',
                'mapped' => false,
            ])
            ->add('isActive', null, [
                'label' => 'Active',
                'required' => false,
                'attr' => [
                    'class' => 'h-5 w-5 text-[#D62828] cursor-pointer'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
