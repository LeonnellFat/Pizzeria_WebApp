<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\OrderItemIngredient;
use App\Entity\Pizza;
use App\Entity\Ingredient;
use App\Entity\User;
use App\Form\OrderType;
use App\Repository\OrderRepository;
use App\Repository\PizzaRepository;
use App\Repository\IngredientRepository;
use App\Service\ActivityLoggerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/order/admin')]
#[IsGranted('ROLE_ADMIN')]
final class OrderAdminController extends AbstractController
{
    public function __construct(
        private ActivityLoggerService $activityLogger,
    ) {}
    #[Route(name: 'app_order_admin_index', methods: ['GET'])]
    public function index(OrderRepository $orderRepository): Response
    {
        $orders = $orderRepository->findAllOrderedByIdDesc();
        
        // Calculate statistics
        $totalOrders = count($orders);
        $totalRevenue = 0;
        $onProcessCount = 0;
        $completedCount = 0;
        $deliveredCount = 0;
        $cancelledCount = 0;

        foreach ($orders as $order) {
            $totalRevenue += $order->getTotalPrice();
            match ($order->getStatus()) {
                'On Process' => $onProcessCount++,
                'Preparing' => $onProcessCount++,
                'Completed' => $completedCount++,
                'Delivered' => $deliveredCount++,
                'Cancelled' => $cancelledCount++,
                default => null,
            };
        }

        return $this->render('admin/order_admin/index.html.twig', [
            'orders' => $orders,
            'totalOrders' => $totalOrders,
            'totalRevenue' => $totalRevenue,
            'onProcessCount' => $onProcessCount,
            'completedCount' => $completedCount,
            'deliveredCount' => $deliveredCount,
            'cancelledCount' => $cancelledCount,
        ]);
    }

    #[Route('/new', name: 'app_order_admin_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, PizzaRepository $pizzaRepository, IngredientRepository $ingredientRepository): Response
    {
        $order = new Order();
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $postData = $request->request->all();
                
                // Clear any items added via form
                foreach ($order->getOrderItems() as $item) {
                    $order->removeOrderItem($item);
                }
                
                // Process items from POST data
                if (isset($postData['order']['orderItems'])) {
                    foreach ($postData['order']['orderItems'] as $itemData) {
                        // Check if it's a custom pizza or premade
                        if (isset($itemData['isCustom']) && $itemData['isCustom'] == '1') {
                            // Custom pizza
                            $quantity = intval($itemData['quantity'] ?? 1);
                            $price = floatval($itemData['price'] ?? 0);
                            
                            $orderItem = new OrderItem();
                            $orderItem->setQuantity($quantity);
                            $orderItem->setIsCustom(true);
                            $orderItem->setFinalPrice($price * $quantity);
                            $order->addOrderItem($orderItem);
                            
                            // Add size, base, cheese as special ingredients with descriptive names
                            if (isset($itemData['size']) && !empty($itemData['size'])) {
                                $sizeIngredient = $ingredientRepository->find((int)$itemData['size']);
                                if ($sizeIngredient) {
                                    $orderItemIngredient = new OrderItemIngredient();
                                    $orderItemIngredient->setIngredient($sizeIngredient);
                                    $orderItemIngredient->setQuantity(1);
                                    $orderItem->addOrderItemIngredient($orderItemIngredient);
                                }
                            }
                            
                            if (isset($itemData['base']) && !empty($itemData['base'])) {
                                $baseIngredient = $ingredientRepository->find((int)$itemData['base']);
                                if ($baseIngredient) {
                                    $orderItemIngredient = new OrderItemIngredient();
                                    $orderItemIngredient->setIngredient($baseIngredient);
                                    $orderItemIngredient->setQuantity(1);
                                    $orderItem->addOrderItemIngredient($orderItemIngredient);
                                }
                            }
                            
                            if (isset($itemData['cheese']) && !empty($itemData['cheese'])) {
                                $cheeseIngredient = $ingredientRepository->find((int)$itemData['cheese']);
                                if ($cheeseIngredient) {
                                    $orderItemIngredient = new OrderItemIngredient();
                                    $orderItemIngredient->setIngredient($cheeseIngredient);
                                    $orderItemIngredient->setQuantity(1);
                                    $orderItem->addOrderItemIngredient($orderItemIngredient);
                                }
                            }
                            
                            // Add toppings/ingredients if provided
                            if (isset($itemData['toppings']) && is_array($itemData['toppings'])) {
                                foreach ($itemData['toppings'] as $toppingKey => $topping) {
                                    // Handle both indexed and associative array formats
                                    $toppingId = is_array($topping) ? ($topping['id'] ?? null) : null;
                                    $toppingQty = is_array($topping) ? (intval($topping['qty'] ?? 1)) : 1;
                                    
                                    if ($toppingId) {
                                        $ingredient = $ingredientRepository->find((int)$toppingId);
                                        if ($ingredient) {
                                            $orderItemIngredient = new OrderItemIngredient();
                                            $orderItemIngredient->setIngredient($ingredient);
                                            $orderItemIngredient->setQuantity($toppingQty);
                                            $orderItem->addOrderItemIngredient($orderItemIngredient);
                                        }
                                    }
                                }
                            }
                        } elseif (isset($itemData['pizza']) && !empty($itemData['pizza'])) {
                            // Premade pizza
                            $pizza = $pizzaRepository->find((int)$itemData['pizza']);
                            if ($pizza) {
                                $quantity = intval($itemData['quantity'] ?? 1);
                                // Add item for each quantity
                                for ($i = 0; $i < $quantity; $i++) {
                                    $orderItem = new OrderItem();
                                    $orderItem->setPizza($pizza);
                                    $orderItem->setQuantity(1);
                                    $orderItem->setIsCustom(false);
                                    $orderItem->setFinalPrice($pizza->getPrice());
                                    $order->addOrderItem($orderItem);
                                }
                            }
                        }
                    }
                }
                
                // Verify order has at least one item
                if ($order->getOrderItems()->isEmpty()) {
                    $this->addFlash('error', 'Order must contain at least one item.');
                    return $this->render('admin/order_admin/new.html.twig', $this->getPizzasAndIngredients($pizzaRepository, $ingredientRepository) + [
                        'order' => $order,
                        'form' => $form,
                    ]);
                }
                
                // Set order details
                $order->setStatus('On Process');
                
                $entityManager->persist($order);
                $entityManager->flush();
                
                // Log the activity
                $user = $this->getUser();
                if ($user instanceof User) {
                    $itemCount = $order->getOrderItems()->count();
                    $this->activityLogger->logActivity(
                        $user,
                        'CREATE',
                        "OrderId:" . $order->getId()
                    );
                }
                
                $this->addFlash('success', 'Order #' . $order->getId() . ' created successfully!');
                return $this->redirectToRoute('app_order_admin_index', [], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error creating order: ' . $e->getMessage());
            }
        }

        return $this->render('admin/order_admin/new.html.twig', $this->getPizzasAndIngredients($pizzaRepository, $ingredientRepository) + [
            'order' => $order,
            'form' => $form,
        ]);
    }
    
    private function getPizzasAndIngredients(PizzaRepository $pizzaRepository, IngredientRepository $ingredientRepository): array
    {
        $pizzas = $pizzaRepository->findAll();
        $ingredients = $ingredientRepository->findAll();
        
        // Convert to JSON for JavaScript
        $pizzasJson = json_encode(array_map(fn(Pizza $p) => [
            'id' => $p->getId(),
            'name' => $p->getName(),
            'price' => $p->getPrice(),
            'image' => $p->getImage() ?? 'pizza-placeholder.png',
        ], $pizzas));
        
        $ingredientsJson = json_encode(array_map(fn(Ingredient $i) => [
            'id' => $i->getId(),
            'name' => $i->getName(),
            'price' => $i->getPrice(),
            'type' => $i->getType() ?? 'topping',
        ], $ingredients));
        
        return [
            'pizzas' => $pizzasJson,
            'ingredients' => $ingredientsJson,
        ];
    }

    #[Route('/{id}', name: 'app_order_admin_show', methods: ['GET'])]
    public function show(Order $order): Response
    {
        return $this->render('admin/order_admin/show.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_order_admin_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Order $order, EntityManagerInterface $entityManager, OrderRepository $orderRepository, PizzaRepository $pizzaRepository, IngredientRepository $ingredientRepository): Response
    {
        // Refresh and eagerly load order items
        $order = $orderRepository->createQueryBuilder('o')
            ->leftJoin('o.orderItems', 'oi')
            ->leftJoin('oi.pizza', 'p')
            ->leftJoin('oi.orderItemIngredients', 'oii')
            ->leftJoin('oii.ingredient', 'i')
            ->addSelect('oi', 'p', 'oii', 'i')
            ->where('o.id = :id')
            ->setParameter('id', $order->getId())
            ->getQuery()
            ->getOneOrNullResult();

        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            // Log the activity
            $user = $this->getUser();
            if ($user instanceof User) {
                $itemCount = $order->getOrderItems()->count();
                $this->activityLogger->logActivity(
                    $user,
                    'UPDATE',
                    "OrderId:" . $order->getId()
                );
            }

            return $this->redirectToRoute('app_order_admin_index', [], Response::HTTP_SEE_OTHER);
        }

        // Convert orderItems collection to array for proper JSON encoding
        $orderItemsArray = [];
        foreach ($order->getOrderItems() as $item) {
            $itemData = [
                'id' => $item->getId(),
                'quantity' => $item->getQuantity(),
                'isCustom' => $item->isCustom(),
                'finalPrice' => $item->getFinalPrice(),
                'pizza' => $item->getPizza() ? ['id' => $item->getPizza()->getId(), 'name' => $item->getPizza()->getName(), 'price' => $item->getPizza()->getPrice()] : null,
                'orderItemIngredients' => []
            ];
            
            foreach ($item->getOrderItemIngredients() as $ing) {
                $itemData['orderItemIngredients'][] = [
                    'id' => $ing->getId(),
                    'quantity' => $ing->getQuantity(),
                    'ingredient' => [
                        'id' => $ing->getIngredient()->getId(),
                        'name' => $ing->getIngredient()->getName(),
                        'type' => $ing->getIngredient()->getType(),
                        'price' => $ing->getIngredient()->getPrice()
                    ]
                ];
            }
            
            $orderItemsArray[] = $itemData;
        }

        return $this->render('admin/order_admin/edit.html.twig', $this->getPizzasAndIngredients($pizzaRepository, $ingredientRepository) + [
            'order' => $order,
            'orderItemsArray' => $orderItemsArray,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_order_admin_delete', methods: ['POST'])]
    public function delete(Request $request, Order $order, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$order->getId(), $request->getPayload()->getString('_token'))) {
            // Log the activity before deletion
            $user = $this->getUser();
            if ($user instanceof User) {
                $itemCount = $order->getOrderItems()->count();
                $this->activityLogger->logActivity(
                    $user,
                    'DELETE',
                    "OrderId: " . $order->getId()
                );
            }

            $entityManager->remove($order);
            $entityManager->flush();
            $this->addFlash('success', 'Order deleted successfully!');
        }

        return $this->redirectToRoute('app_order_admin_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/status', name: 'app_order_admin_update_status', methods: ['POST'])]
    public function updateStatus(Request $request, Order $order, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('status'.$order->getId(), $request->getPayload()->getString('_token'))) {
            $oldStatus = $order->getStatus();
            $status = $request->getPayload()->getString('status');
            $validStatuses = ['On Process', 'Preparing', 'Completed', 'Delivered', 'Cancelled'];
            
            if (in_array($status, $validStatuses)) {
                $order->setStatus($status);
                $entityManager->flush();

                // Log the activity
                $user = $this->getUser();
                if ($user instanceof User) {
                    $this->activityLogger->logActivity(
                        $user,
                        'UPDATE_ORDER_STATUS',
                        "OrderId: " . $order->getId() . " Status changed from " . $oldStatus . " to " . $status
                    );
                }

                $this->addFlash('success', 'Order status updated successfully!');
            } else {
                $this->addFlash('error', 'Invalid status provided.');
            }
        }

        return $this->redirectToRoute('app_order_admin_index', [], Response::HTTP_SEE_OTHER);
    }
}
