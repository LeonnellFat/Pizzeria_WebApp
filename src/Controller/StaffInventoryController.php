<?php

namespace App\Controller;

use App\Entity\PizzaStock;
use App\Entity\IngredientStock;
use App\Repository\PizzaRepository;
use App\Repository\PizzaStockRepository;
use App\Repository\IngredientRepository;
use App\Repository\IngredientStockRepository;
use App\Service\ActivityLoggerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/staff/inventory')]
#[IsGranted('ROLE_STAFF')]
final class StaffInventoryController extends AbstractController
{
    public function __construct(
        private ActivityLoggerService $activityLogger,
    ) {}

    #[Route(name: 'app_staff_inventory')]
    public function index(
        PizzaStockRepository $pizzaStockRepo,
        IngredientStockRepository $ingredientStockRepo
    ): Response
    {
        // Get all pizza stocks with their related pizzas
        $pizzaStocks = $pizzaStockRepo->findAll();
        
        // Get all ingredient stocks with their related ingredients
        $ingredientStocks = $ingredientStockRepo->findAll();

        return $this->render('staff/inventory/index.html.twig', [
            'pizzaStocks' => $pizzaStocks,
            'ingredientStocks' => $ingredientStocks,
        ]);
    }

    #[Route('/pizza/{id}', name: 'app_staff_inventory_pizza_details')]
    public function pizzaDetails(int $id, PizzaStockRepository $pizzaStockRepo): Response
    {
        $pizzaStock = $pizzaStockRepo->find($id);

        if (!$pizzaStock) {
            throw $this->createNotFoundException('Pizza stock not found');
        }

        return $this->render('staff/inventory/pizza_details.html.twig', [
            'pizzaStock' => $pizzaStock,
        ]);
    }

    #[Route('/ingredient/{id}', name: 'app_staff_inventory_ingredient_details')]
    public function ingredientDetails(int $id, IngredientStockRepository $ingredientStockRepo): Response
    {
        $ingredientStock = $ingredientStockRepo->find($id);

        if (!$ingredientStock) {
            throw $this->createNotFoundException('Ingredient stock not found');
        }

        return $this->render('staff/inventory/ingredient_details.html.twig', [
            'ingredientStock' => $ingredientStock,
        ]);
    }

    #[Route('/pizza/{id}/edit', name: 'app_staff_inventory_pizza_edit')]
    public function editPizzaStock(
        int $id,
        Request $request,
        PizzaStockRepository $pizzaStockRepo,
        EntityManagerInterface $em
    ): Response
    {
        $pizzaStock = $pizzaStockRepo->find($id);

        if (!$pizzaStock) {
            throw $this->createNotFoundException('Pizza stock not found');
        }

        if ($request->isMethod('POST')) {
            $oldQuantity = $pizzaStock->getQuantity();
            $quantity = $request->request->get('quantity');
            $addQuantity = $request->request->get('addQuantity');
            
            // If addQuantity is provided and not empty, add it to current quantity
            if ($addQuantity !== '' && $addQuantity !== null) {
                $quantity = $pizzaStock->getQuantity() + (int)$addQuantity;
            } else {
                $quantity = (int)$quantity;
            }
            
            $lastRestocked = new \DateTimeImmutable();

            $pizzaStock->setQuantity($quantity);
            $pizzaStock->setLastRestocked($lastRestocked);

            $em->flush();

            // Log the activity
            $user = $this->getUser();
            if ($user instanceof \App\Entity\User) {
                $this->activityLogger->logActivity(
                    $user,
                    'UPDATE',
                    "Pizza: {$pizzaStock->getPizza()->getName()} ID: {$pizzaStock->getId()}"
                );
            }

            $this->addFlash('success', 'Pizza stock updated successfully!');
            return $this->redirectToRoute('app_staff_inventory');
        }

        return $this->render('staff/inventory/edit_pizza_stock.html.twig', [
            'pizzaStock' => $pizzaStock,
        ]);
    }

    #[Route('/ingredient/{id}/edit', name: 'app_staff_inventory_ingredient_edit')]
    public function editIngredientStock(
        int $id,
        Request $request,
        IngredientStockRepository $ingredientStockRepo,
        EntityManagerInterface $em
    ): Response
    {
        $ingredientStock = $ingredientStockRepo->find($id);

        if (!$ingredientStock) {
            throw $this->createNotFoundException('Ingredient stock not found');
        }

        if ($request->isMethod('POST')) {
            $oldQuantity = $ingredientStock->getQuantity();
            $quantity = $request->request->get('quantity');
            $addQuantity = $request->request->get('addQuantity');
            
            // If addQuantity is provided and not empty, add it to current quantity
            if ($addQuantity !== '' && $addQuantity !== null) {
                $quantity = $ingredientStock->getQuantity() + (int)$addQuantity;
            } else {
                $quantity = (int)$quantity;
            }
            
            $lastRestocked = new \DateTimeImmutable();

            $ingredientStock->setQuantity($quantity);
            $ingredientStock->setLastRestocked($lastRestocked);

            $em->flush();

            // Log the activity
            $user = $this->getUser();
            if ($user instanceof \App\Entity\User) {
                $this->activityLogger->logActivity(
                    $user,
                    'UPDATE',
                    "Entity: Ingredient Stock | Ingredient: {$ingredientStock->getIngredient()->getName()} (ID: {$ingredientStock->getId()})"
                );
            }
            $this->addFlash('success', 'Ingredient stock updated successfully!');
            return $this->redirectToRoute('app_staff_inventory');
        }

        return $this->render('staff/inventory/edit_ingredient_stock.html.twig', [
            'ingredientStock' => $ingredientStock,
        ]);
    }
}
