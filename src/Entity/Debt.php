<?php

namespace App\Entity;

use App\Repository\DebtRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DebtRepository::class)]
class Debt
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'debt')]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[ORM\ManyToOne(targetEntity: Stock::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $stock;

    #[ORM\Column(type: 'float')]
    private $cdn;

    #[ORM\Column(type: 'float')]
    private $usd;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getStock(): ?Stock
    {
        return $this->stock;
    }

    public function setStock(?Stock $stock): self
    {
        $this->stock = $stock;

        return $this;
    }

    public function getUsd(): ?float
    {
        return $this->usd;
    }

    public function setUsd(float $usd): self
    {
        $this->usd = $usd;

        return $this;
    }

    public function getCdn(): ?float
    {
        return $this->cdn;
    }

    public function setCdn(float $cdn): self
    {
        $this->cdn = $cdn;

        return $this;
    }

}
