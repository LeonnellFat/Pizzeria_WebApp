<?php

namespace App\Form;

use App\Entity\OrderItemIngredient;
use App\Entity\Ingredient;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderItemIngredientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ingredient', EntityType::class, [
                'class' => Ingredient::class,
                'choice_label' => 'name',
                'label' => 'Ingredient',
                'placeholder' => '-- Select an ingredient --',
                'attr' => [
                    'class' => 'w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-[#D62828]'
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OrderItemIngredient::class,
        ]);
    }
}
