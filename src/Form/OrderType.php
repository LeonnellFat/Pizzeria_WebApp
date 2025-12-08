<?php

namespace App\Form;

use App\Entity\Order;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class OrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('customerName', TextType::class, [
                'label' => 'Customer Name',
                'constraints' => [
                    new NotBlank(['message' => 'Customer name is required.']),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Customer name must be at least 2 characters.',
                        'max' => 255,
                    ]),
                ],
                'attr' => [
                    'class' => 'w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-[#D62828]',
                    'placeholder' => 'Enter customer name'
                ]
            ])
            ->add('customerPhone', TelType::class, [
                'label' => 'Phone Number',
                'constraints' => [
                    new NotBlank(['message' => 'Phone number is required.']),
                ],
                'attr' => [
                    'class' => 'w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-[#D62828]',
                    'placeholder' => 'Enter phone number'
                ]
            ])
            ->add('customerAddress', TextType::class, [
                'label' => 'Delivery Address',
                'constraints' => [
                    new NotBlank(['message' => 'Address is required.']),
                    new Length([
                        'min' => 5,
                        'minMessage' => 'Address must be at least 5 characters.',
                        'max' => 255,
                    ]),
                ],
                'attr' => [
                    'class' => 'w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-[#D62828]',
                    'placeholder' => 'Enter delivery address'
                ]
            ])
            ->add('orderItems', CollectionType::class, [
                'entry_type' => OrderItemType::class,
                'entry_options' => ['label' => false],
                'label' => 'Order Items',
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'prototype_name' => '__itemindex__',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
            'csrf_protection' => true,
            'allow_extra_fields' => true,
        ]);
    }
}

