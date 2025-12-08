<?php

namespace App\Entity;

use App\Repository\OrderItemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderItemRepository::class)]
class OrderItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?bool $isCustom = null;

    #[ORM\Column]
    private ?int $quantity = null;

    #[ORM\Column]
    private ?float $finalPrice = null;

    #[ORM\ManyToOne(inversedBy: 'orderItems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Order $parentOrder = null;

    /**
     * @var Collection<int, OrderItemIngredient>
     */
    #[ORM\OneToMany(targetEntity: OrderItemIngredient::class, mappedBy: 'orderItem', orphanRemoval: true, cascade: ['persist'])]
    private Collection $orderItemIngredients;

    #[ORM\ManyToOne]
    private ?Pizza $pizza = null;

    public function __construct()
    {
        $this->orderItemIngredients = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isCustom(): ?bool
    {
        return $this->isCustom;
    }

    public function setIsCustom(bool $isCustom): static
    {
        $this->isCustom = $isCustom;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getFinalPrice(): ?float
    {
        return $this->finalPrice;
    }

    public function setFinalPrice(float $finalPrice): static
    {
        $this->finalPrice = $finalPrice;

        return $this;
    }

    public function getParentOrder(): ?Order
    {
        return $this->parentOrder;
    }

    public function setParentOrder(?Order $parentOrder): static
    {
        $this->parentOrder = $parentOrder;

        return $this;
    }

    /**
     * @return Collection<int, OrderItemIngredient>
     */
    public function getOrderItemIngredients(): Collection
    {
        return $this->orderItemIngredients;
    }

    public function addOrderItemIngredient(OrderItemIngredient $orderItemIngredient): static
    {
        if (!$this->orderItemIngredients->contains($orderItemIngredient)) {
            $this->orderItemIngredients->add($orderItemIngredient);
            $orderItemIngredient->setOrderItem($this);
        }

        return $this;
    }

    public function removeOrderItemIngredient(OrderItemIngredient $orderItemIngredient): static
    {
        if ($this->orderItemIngredients->removeElement($orderItemIngredient)) {
            // set the owning side to null (unless already changed)
            if ($orderItemIngredient->getOrderItem() === $this) {
                $orderItemIngredient->setOrderItem(null);
            }
        }

        return $this;
    }

    public function getPizza(): ?Pizza
    {
        return $this->pizza;
    }

    public function setPizza(?Pizza $pizza): static
    {
        $this->pizza = $pizza;

        return $this;
    }
}
