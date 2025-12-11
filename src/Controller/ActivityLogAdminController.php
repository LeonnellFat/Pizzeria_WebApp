<?php

namespace App\Controller;

use App\Repository\ActivityLogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/activity-log/admin')]
#[IsGranted('ROLE_ADMIN')]
final class ActivityLogAdminController extends AbstractController
{
    #[Route(name: 'app_activity_log_index', methods: ['GET'])]
    public function index(Request $request, ActivityLogRepository $activityLogRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        $filterUser = $request->query->getString('filterUser', '');
        $filterAction = $request->query->getString('filterAction', '');
        $filterDate = $request->query->getString('filterDate', '');

        // Build query
        $queryBuilder = $activityLogRepository->createQueryBuilder('a')
            ->orderBy('a.dateTime', 'DESC');

        // Apply filters
        if (!empty($filterUser)) {
            $queryBuilder->andWhere('a.username LIKE :username')
                ->setParameter('username', '%' . $filterUser . '%');
        }

        if (!empty($filterAction)) {
            $queryBuilder->andWhere('a.action = :action')
                ->setParameter('action', $filterAction);
        }

        if (!empty($filterDate)) {
            $filterDate = new \DateTime($filterDate);
            $queryBuilder->andWhere('DATE(a.dateTime) = :date')
                ->setParameter('date', $filterDate->format('Y-m-d'));
        }

        $total = $activityLogRepository->count([]);
        $perPage = 50;
        $offset = ($page - 1) * $perPage;
        
        $logs = $queryBuilder
            ->setFirstResult($offset)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getResult();

        $totalPages = ceil($total / $perPage);

        return $this->render('admin/activity_log/index.html.twig', [
            'logs' => $logs,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'filterUser' => $filterUser,
            'filterAction' => $filterAction,
            'filterDate' => $filterDate,
        ]);
    }

    #[Route('/{id}', name: 'app_activity_log_show', methods: ['GET'])]
    public function show(int $id, ActivityLogRepository $activityLogRepository): Response
    {
        $log = $activityLogRepository->find($id);

        if (!$log) {
            throw $this->createNotFoundException('Activity log not found.');
        }

        return $this->render('admin/activity_log/show.html.twig', [
            'log' => $log,
        ]);
    }
}
