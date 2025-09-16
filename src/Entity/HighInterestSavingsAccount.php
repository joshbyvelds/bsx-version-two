<?php

namespace App\Entity;

use App\Repository\HighInterestSavingsAccountRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HighInterestSavingsAccountRepository::class)]
class HighInterestSavingsAccount
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\Column(type: 'string', length: 255)]
    private $bank;

    #[ORM\Column(type: 'float')]
    private $amount;

    #[ORM\Column(type: 'float')]
    private $interest_rate;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'highInterestSavingsAccounts')]
    private $user;

    #[ORM\Column(type: 'string', length: 5)]
    private $currency;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getBank(): ?string
    {
        return $this->bank;
    }

    public function setBank(string $bank): self
    {
        $this->bank = $bank;

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

    public function getInterestRate(): ?float
    {
        return $this->interest_rate;
    }

    public function setInterestRate(float $interest_rate): self
    {
        $this->interest_rate = $interest_rate;

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

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function withdraw(float $amount): self
    {
        $this->amount -= $amount;

        return $this;
    }

    public function deposit(float $amount): self
    {
        $this->amount += $amount;

        return $this;
    }
}
