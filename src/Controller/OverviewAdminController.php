<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use App\Repository\PizzaRepository;
use App\Repository\CategoryRepository;
use App\Repository\IngredientRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;

final class OverviewAdminController extends AbstractController
{
    #[Route('/overview/admin', name: 'app_overview_admin')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(
        OrderRepository $orderRepo,
        PizzaRepository $pizzaRepo,
        CategoryRepository $categoryRepo,
        IngredientRepository $ingredientRepo,
        UserRepository $userRepo,
        Request $request,
        EntityManagerInterface $em
    ): Response
    {
        // Check if this is an AJAX request for chart data only
        if ($request->query->get('ajax') === '1') {
            $filter = $request->query->get('filter', 'this_month');
            $revenueData = $this->getRevenueChartData($orderRepo, $filter);
            
            return $this->json($revenueData);
        }

        // Get all statistics
        $totalOrders = $orderRepo->count([]);
        $totalPizzas = $pizzaRepo->count([]);
        $totalCategories = $categoryRepo->count([]);
        $totalIngredients = $ingredientRepo->count([]);
        $totalUsers = $userRepo->count([]);

        // Calculate total revenue
        $totalRevenue = 0;
        $allOrders = $orderRepo->findAll();
        foreach ($allOrders as $order) {
            $totalRevenue += $order->getTotalPrice();
        }

        // Get filter parameter
        $filter = $request->query->get('filter', 'this_month');

        // Get revenue data based on filter
        $revenueData = $this->getRevenueChartData($orderRepo, $filter);

        return $this->render('admin/overview_admin/index.html.twig', [
            'totalOrders' => $totalOrders,
            'totalRevenue' => $totalRevenue,
            'totalPizzas' => $totalPizzas,
            'totalCategories' => $totalCategories,
            'totalIngredients' => $totalIngredients,
            'totalUsers' => $totalUsers,
            'revenueData' => $revenueData,
            'filter' => $filter,
        ]);
    }

    private function getRevenueChartData(OrderRepository $orderRepo, string $filter): array
    {
        $now = new \DateTime();
        $orders = [];

        switch ($filter) {
            case 'today':
                $startDate = (new \DateTime())->setTime(0, 0, 0);
                $endDate = (new \DateTime())->setTime(23, 59, 59);
                $orders = $orderRepo->findOrdersByDateRange($startDate, $endDate);
                $labels = $this->generateHourlyLabels();
                break;
            case 'this_week':
                $startDate = (new \DateTime())->modify('-6 days')->setTime(0, 0, 0);
                $endDate = (new \DateTime())->setTime(23, 59, 59);
                $orders = $orderRepo->findOrdersByDateRange($startDate, $endDate);
                $labels = $this->generateDailyLabels(7);
                break;
            case 'this_month':
            default:
                $startDate = (new \DateTime())->modify('first day of this month')->setTime(0, 0, 0);
                $endDate = (new \DateTime())->setTime(23, 59, 59);
                $orders = $orderRepo->findOrdersByDateRange($startDate, $endDate);
                $labels = $this->generateMonthlyDayLabels();
                break;
        }

        // Aggregate revenue by period
        $revenueByPeriod = [];
        foreach ($labels as $label) {
            $revenueByPeriod[$label] = 0;
        }

        foreach ($orders as $order) {
            $key = $this->getRevenueKey($order->getCreatedAt(), $filter);
            if (isset($revenueByPeriod[$key])) {
                $revenueByPeriod[$key] += $order->getTotalPrice();
            }
        }

        return [
            'labels' => array_keys($revenueByPeriod),
            'data' => array_values($revenueByPeriod),
        ];
    }

    private function getRevenueKey(\DateTimeImmutable $date, string $filter): string
    {
        $dateTime = new \DateTime($date->format('Y-m-d H:i:s'));
        switch ($filter) {
            case 'today':
                return $dateTime->format('H:00');
            case 'this_week':
                return $dateTime->format('M d');
            case 'this_month':
            default:
                return $dateTime->format('d');
        }
    }

    private function generateHourlyLabels(): array
    {
        $labels = [];
        for ($i = 0; $i < 24; $i++) {
            $labels[] = str_pad($i, 2, '0', STR_PAD_LEFT) . ':00';
        }
        return $labels;
    }

    private function generateDailyLabels(int $days): array
    {
        $labels = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = new \DateTime();
            $date->modify("-$i days");
            $labels[] = $date->format('M d');
        }
        return $labels;
    }

    private function generateMonthlyDayLabels(): array
    {
        $now = new \DateTime();
        $daysInMonth = (int)$now->format('t');
        $labels = [];
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $labels[] = str_pad($i, 2, '0', STR_PAD_LEFT);
        }
        return $labels;
    }
}
