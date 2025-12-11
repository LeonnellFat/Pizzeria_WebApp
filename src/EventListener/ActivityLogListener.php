<?php

namespace App\EventListener;

use App\Entity\ActivityLog;
use App\Entity\Pizza;
use App\Entity\Category;
use App\Entity\Ingredient;
use App\Entity\Order;
use App\Entity\User;
use App\Entity\PizzaStock;
use App\Entity\IngredientStock;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsDoctrineListener(event: Events::postPersist)]
#[AsDoctrineListener(event: Events::postUpdate)]
#[AsDoctrineListener(event: Events::preRemove)]
final class ActivityLogListener
{
    private array $entitiesToTrack = [
        Pizza::class,
        Category::class,
        Ingredient::class,
        Order::class,
        User::class,
        PizzaStock::class,
        IngredientStock::class,
    ];

    public function __construct(
        private Security $security,
        private RequestStack $requestStack,
    ) {}

    public function postPersist(PostPersistEventArgs $args): void
    {
        $entity = $args->getObject();
        
        if (!$this->shouldTrack($entity)) {
            return;
        }

        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return;
        }

        $log = new ActivityLog();
        $log->setUserId($user);
        $log->setUsername($user->getUsername());
        $log->setRole(implode(', ', $user->getRoles()));
        $log->setAction('CREATE');
        $log->setTargetData($this->getEntityName($entity));
        $log->setDateTime(new \DateTimeImmutable('now', new \DateTimeZone('Asia/Singapore')));
        $log->setIpAddress($this->getClientIp());

        $args->getObjectManager()->persist($log);
        $args->getObjectManager()->flush();
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        
        if (!$this->shouldTrack($entity)) {
            return;
        }

        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return;
        }

        $log = new ActivityLog();
        $log->setUserId($user);
        $log->setUsername($user->getUsername());
        $log->setRole(implode(', ', $user->getRoles()));
        $log->setAction('UPDATE');
        $log->setTargetData($this->getEntityName($entity));
        $log->setDateTime(new \DateTimeImmutable('now', new \DateTimeZone('Asia/Singapore')));
        $log->setIpAddress($this->getClientIp());

        $args->getObjectManager()->persist($log);
        $args->getObjectManager()->flush();
    }

    public function preRemove(PreRemoveEventArgs $args): void
    {
        $entity = $args->getObject();
        
        if (!$this->shouldTrack($entity)) {
            return;
        }

        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return;
        }

        $log = new ActivityLog();
        $log->setUserId($user);
        $log->setUsername($user->getUsername());
        $log->setRole(implode(', ', $user->getRoles()));
        $log->setAction('DELETE');
        $log->setTargetData($this->getEntityName($entity));
        $log->setDateTime(new \DateTimeImmutable('now', new \DateTimeZone('Asia/Singapore')));
        $log->setIpAddress($this->getClientIp());

        $args->getObjectManager()->persist($log);
    }

    private function shouldTrack(object $entity): bool
    {
        foreach ($this->entitiesToTrack as $class) {
            if ($entity instanceof $class) {
                return true;
            }
        }
        return false;
    }

    private function getEntityName(object $entity): string
    {
        $class = $entity::class;
        return substr($class, strrpos($class, '\\') + 1);
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
