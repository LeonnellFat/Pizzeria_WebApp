<?php

namespace App\Form;

use App\Entity\OrderItem;
use App\Entity\Pizza;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('itemType', ChoiceType::class, [
                'label' => 'Item Type',
                'choices' => [
                    'Premade Pizza' => 'premade',
                    'Custom Pizza' => 'custom',
                ],
                'mapped' => false,
                'attr' => [
                    'class' => 'w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-[#D62828]',
                    'data-toggle-custom-fields' => 'true'
                ]
            ])
            ->add('pizza', EntityType::class, [
                'class' => Pizza::class,
                'choice_label' => 'name',
                'label' => 'Select Pizza',
                'required' => false,
                'placeholder' => '-- Choose a pizza --',
                'attr' => [
                    'class' => 'w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-[#D62828]',
                    'data-premade-field' => 'true'
                ]
            ])
            ->add('quantity', IntegerType::class, [
                'label' => 'Quantity',
                'attr' => [
                    'class' => 'w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-[#D62828]',
                    'min' => 1,
                    'value' => 1
                ]
            ])
            ->add('isCustom', CheckboxType::class, [
                'label' => 'Is Custom Pizza',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'class' => 'h-5 w-5 text-[#D62828] cursor-pointer',
                    'data-custom-checkbox' => 'true'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OrderItem::class,
            'allow_extra_fields' => true,
        ]);
    }
}
