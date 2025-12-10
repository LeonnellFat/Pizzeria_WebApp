<?php

namespace App\Controller;

use App\Entity\PizzaStock;
use App\Entity\IngredientStock;
use App\Repository\PizzaRepository;
use App\Repository\PizzaStockRepository;
use App\Repository\IngredientRepository;
use App\Repository\IngredientStockRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;

final class InventoryAdminController extends AbstractController
{
    #[Route('/inventory/admin', name: 'app_inventory_admin')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(
        PizzaStockRepository $pizzaStockRepo,
        IngredientStockRepository $ingredientStockRepo
    ): Response
    {
        // Get all pizza stocks with their related pizzas
        $pizzaStocks = $pizzaStockRepo->findAll();
        
        // Get all ingredient stocks with their related ingredients
        $ingredientStocks = $ingredientStockRepo->findAll();

        return $this->render('inventory_admin/index.html.twig', [
            'pizzaStocks' => $pizzaStocks,
            'ingredientStocks' => $ingredientStocks,
        ]);
    }

    #[Route('/inventory/admin/pizza/{id}', name: 'app_inventory_admin_pizza_details')]
    public function pizzaDetails(int $id, PizzaStockRepository $pizzaStockRepo): Response
    {
        $pizzaStock = $pizzaStockRepo->find($id);

        if (!$pizzaStock) {
            throw $this->createNotFoundException('Pizza stock not found');
        }

        return $this->render('inventory_admin/pizza_details.html.twig', [
            'pizzaStock' => $pizzaStock,
        ]);
    }

    #[Route('/inventory/admin/ingredient/{id}', name: 'app_inventory_admin_ingredient_details')]
    public function ingredientDetails(int $id, IngredientStockRepository $ingredientStockRepo): Response
    {
        $ingredientStock = $ingredientStockRepo->find($id);

        if (!$ingredientStock) {
            throw $this->createNotFoundException('Ingredient stock not found');
        }

        return $this->render('inventory_admin/ingredient_details.html.twig', [
            'ingredientStock' => $ingredientStock,
        ]);
    }

    #[Route('/inventory/admin/pizza/{id}/edit', name: 'app_inventory_admin_pizza_edit')]
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
            $quantity = (int)$request->request->get('quantity');
            $lastRestocked = new \DateTimeImmutable();

            $pizzaStock->setQuantity($quantity);
            $pizzaStock->setLastRestocked($lastRestocked);

            $em->flush();

            $this->addFlash('success', 'Pizza stock updated successfully!');
            return $this->redirectToRoute('app_inventory_admin');
        }

        return $this->render('inventory_admin/edit_pizza_stock.html.twig', [
            'pizzaStock' => $pizzaStock,
        ]);
    }

    #[Route('/inventory/admin/ingredient/{id}/edit', name: 'app_inventory_admin_ingredient_edit')]
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
            $quantity = (int)$request->request->get('quantity');
            $lastRestocked = new \DateTimeImmutable();

            $ingredientStock->setQuantity($quantity);
            $ingredientStock->setLastRestocked($lastRestocked);

            $em->flush();

            $this->addFlash('success', 'Ingredient stock updated successfully!');
            return $this->redirectToRoute('app_inventory_admin');
        }

        return $this->render('inventory_admin/edit_ingredient_stock.html.twig', [
            'ingredientStock' => $ingredientStock,
        ]);
    }
}
