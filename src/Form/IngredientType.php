<?php

namespace App\Form;

use App\Entity\Ingredient;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IngredientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Size' => 'Size',
                    'Base' => 'Base',
                    'Cheese' => 'Cheese',
                    'Topping' => 'Topping',
                ],
                'placeholder' => 'Select type',
            ])
            ->add('price')
            ->add('isAvailable');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ingredient::class,
        ]);
    }
}
