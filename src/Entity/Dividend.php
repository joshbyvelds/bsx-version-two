<?php

namespace App\Entity;

use App\Repository\DividendRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DividendRepository::class)]
class Dividend
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Stock::class, inversedBy: 'dividends')]
    #[ORM\JoinColumn(nullable: false)]
    private $Stock;

    #[ORM\Column(type: 'date')]
    private $payment_date;

    #[ORM\Column(type: 'float')]
    private $amount;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'dividends')]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStock(): ?Stock
    {
        return $this->Stock;
    }

    public function setStock(?Stock $Stock): self
    {
        $this->Stock = $Stock;

        return $this;
    }

    public function getPaymentDate(): ?\DateTimeInterface
    {
        return $this->payment_date;
    }

    public function setPaymentDate(\DateTimeInterface $payment_date): self
    {
        $this->payment_date = $payment_date;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

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
}
