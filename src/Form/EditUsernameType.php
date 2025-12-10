<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class EditUsernameType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', null, [
                'label' => 'New Username',
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
                    'placeholder' => 'Enter new username'
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
