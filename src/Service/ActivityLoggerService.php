<?php

namespace App\Service;

use App\Entity\ActivityLog;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ActivityLoggerService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RequestStack $requestStack,
    ) {}

    /**
     * Log an activity to the ActivityLog table
     */
    public function logActivity(
        ?User $user,
        string $action,
        string $targetData,
        ?string $role = null
    ): void
    {
        if (!$user instanceof User) {
            return;
        }

        $log = new ActivityLog();
        $log->setUserId($user);
        $log->setUsername($user->getUsername() ?? 'Unknown');
        $log->setRole($role ?? implode(', ', $user->getRoles()));
        $log->setAction($action);
        $log->setTargetData($targetData);
        $log->setDateTime(new \DateTimeImmutable('now', new \DateTimeZone('Asia/Singapore')));
        $log->setIpAddress($this->getClientIp());
        
        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    /**
     * Get client IP address
     */
    private function getClientIp(): ?string
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return null;
        }
        return $request->getClientIp();
    }
}
