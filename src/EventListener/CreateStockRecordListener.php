<?php

namespace App\EventListener;

use App\Entity\Pizza;
use App\Entity\Ingredient;
use App\Entity\PizzaStock;
use App\Entity\IngredientStock;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(event: Events::postPersist)]
final class CreateStockRecordListener
{
    public function postPersist(PostPersistEventArgs $args): void
    {
        $entity = $args->getObject();
        $em = $args->getObjectManager();

        // creates the pizza stock if an pizza is created
        if ($entity instanceof Pizza) {
            $pizzaStock = new PizzaStock();
            $pizzaStock->setPizza($entity);
            $pizzaStock->setQuantity(0);
            
            $em->persist($pizzaStock);
            $em->flush();
        }

        // creates the ingredient stock if an ingredient is created
        if ($entity instanceof Ingredient) {
            $ingredientStock = new IngredientStock();
            $ingredientStock->setIngredient($entity);
            $ingredientStock->setQuantity(0);
            
            $em->persist($ingredientStock);
            $em->flush();
        }
    }
}
