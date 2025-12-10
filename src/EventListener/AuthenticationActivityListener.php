<?php

namespace App\EventListener;

use App\Entity\ActivityLog;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;

#[AsEventListener(event: LoginSuccessEvent::class, method: 'onLoginSuccess')]
#[AsEventListener(event: LogoutEvent::class, method: 'onLogout')]
final class AuthenticationActivityListener
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RequestStack $requestStack,
    ) {}

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        
        if (!$user instanceof User) {
            return;
        }

        $log = new ActivityLog();
        $log->setUserId($user);
        $log->setUsername($user->getUsername());
        $log->setRole(implode(', ', $user->getRoles()));
        $log->setAction('LOGIN');
        $log->setTargetData('User');
        $log->setDateTime(new \DateTimeImmutable());
        $log->setIpAddress($this->getClientIp());

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    public function onLogout(LogoutEvent $event): void
    {
        $user = $event->getToken()?->getUser();
        
        if (!$user instanceof User) {
            return;
        }

        $log = new ActivityLog();
        $log->setUserId($user);
        $log->setUsername($user->getUsername());
        $log->setRole(implode(', ', $user->getRoles()));
        $log->setAction('LOGOUT');
        $log->setTargetData('User');
        $log->setDateTime(new \DateTimeImmutable());
        $log->setIpAddress($this->getClientIp());

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    private function getClientIp(): ?string
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return null;
        }

        return $request->getClientIp();
    }
}
