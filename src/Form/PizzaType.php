<?php

namespace App\Form;

use App\Entity\Pizza;
use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class PizzaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('price')
            ->add('category', EntityType::class, [
                'class' => Category::class,
                // shows category names in dropdown
                'choice_label' => 'name', 
                'placeholder' => 'Select a Category',
                'required' => true,
            ])
            ->add('isAvailable')
            ->add('image', FileType::class, [
                'label' => 'Pizza Image',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'accept' => 'image/*',
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Pizza::class,
        ]);
    }
}
