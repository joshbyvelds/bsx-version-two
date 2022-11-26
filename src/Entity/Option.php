<?php

namespace App\Entity;

use App\Repository\OptionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OptionRepository::class)]
#[ORM\Table(name: '`option`')]
class Option
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Stock::class, inversedBy: 'options')]
    #[ORM\JoinColumn(nullable: false)]
    private $stock;

    #[ORM\Column(type: 'integer')]
    private $type;

    #[ORM\Column(type: 'float')]
    private $strike;

    #[ORM\Column(type: 'integer')]
    private $contracts;

    #[ORM\Column(type: 'float')]
    private $average;

    #[ORM\Column(type: 'integer')]
    private $buys;

    #[ORM\Column(type: 'date')]
    private $expiry;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'options')]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\Column(type: 'boolean')]
    private $expired;

    #[ORM\Column(type: 'float')]
    private $current;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getStrike(): ?float
    {
        return $this->strike;
    }

    public function setStrike(float $strike): self
    {
        $this->strike = $strike;

        return $this;
    }

    public function getContracts(): ?int
    {
        return $this->contracts;
    }

    public function setContracts(int $contracts): self
    {
        $this->contracts = $contracts;

        return $this;
    }

    public function getAverage(): ?float
    {
        return $this->average;
    }

    public function setAverage(float $average): self
    {
        $this->average = $average;

        return $this;
    }

    public function getBuys(): ?int
    {
        return $this->buys;
    }

    public function setBuys(int $buys): self
    {
        $this->buys = $buys;

        return $this;
    }

    public function getExpiry(): ?\DateTimeInterface
    {
        return $this->expiry;
    }

    public function setExpiry(\DateTimeInterface $expiry): self
    {
        $this->expiry = $expiry;

        return $this;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function isExpired(): ?bool
    {
        return $this->expired;
    }

    public function setExpired(bool $expired): self
    {
        $this->expired = $expired;

        return $this;
    }

    public function getCurrent(): ?float
    {
        return $this->current;
    }

    public function setCurrent(float $current): self
    {
        $this->current = $current;

        return $this;
    }
}
