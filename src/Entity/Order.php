<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: '`order`')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $customerName = null;

    #[ORM\Column(length: 20)]
    private ?string $customerPhone = null;

    #[ORM\Column(length: 255)]
    private ?string $customerAddress = null;

    #[ORM\Column(length: 50)]
    private ?string $status = "On Process"; // default value

    #[ORM\Column]
    private ?float $totalPrice = 0;

    #[ORM\Column]
    private ?int $totalQuantity = 0;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToMany(targetEntity: OrderItem::class, mappedBy: 'parentOrder', orphanRemoval: true, cascade: ['persist'])]
    private Collection $orderItems;

    public function __construct()
    {
        $this->orderItems = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        if (!$this->createdAt) {
            $this->createdAt = new \DateTimeImmutable();
        }

        $this->recalculateTotals();
    }

    // Auto recalc totals when modifying items
    public function recalculateTotals(): void
    {
        $this->totalPrice = 0;
        $this->totalQuantity = 0;

        foreach ($this->orderItems as $item) {
            $this->totalPrice += $item->getFinalPrice() * $item->getQuantity();
            $this->totalQuantity += $item->getQuantity();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCustomerName(): ?string
    {
        return $this->customerName;
    }

    public function setCustomerName(string $customerName): static
    {
        $this->customerName = $customerName;
        return $this;
    }

    public function getCustomerPhone(): ?string
    {
        return $this->customerPhone;
    }

    public function setCustomerPhone(string $customerPhone): static
    {
        $this->customerPhone = $customerPhone;
        return $this;
    }

    public function getCustomerAddress(): ?string
    {
        return $this->customerAddress;
    }

    public function setCustomerAddress(string $customerAddress): static
    {
        $this->customerAddress = $customerAddress;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getTotalPrice(): ?float
    {
        return $this->totalPrice;
    }

    public function getTotalQuantity(): ?int
    {
        return $this->totalQuantity;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return Collection<int, OrderItem>
     */
    public function getOrderItems(): Collection
    {
        return $this->orderItems;
    }

    public function addOrderItem(OrderItem $orderItem): static
    {
        if (!$this->orderItems->contains($orderItem)) {
            $this->orderItems->add($orderItem);
            $orderItem->setParentOrder($this);
            $this->recalculateTotals();
        }

        return $this;
    }

    public function removeOrderItem(OrderItem $orderItem): static
    {
        if ($this->orderItems->removeElement($orderItem)) {
            if ($orderItem->getParentOrder() === $this) {
                $orderItem->setParentOrder(null);
            }
            $this->recalculateTotals();
        }

        return $this;
    }
}
