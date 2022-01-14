<?php

namespace App\Entity;

use App\Repository\WalletRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WalletRepository::class)]
class Wallet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\OneToOne(inversedBy: 'wallet', targetEntity: User::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private $User;

    #[ORM\Column(type: 'float')]
    private $CAN;

    #[ORM\Column(type: 'float')]
    private $USD;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->User;
    }

    public function setUser(User $User): self
    {
        $this->User = $User;

        return $this;
    }

    public function getCAN(): ?float
    {
        return $this->CAN;
    }

    public function setCAN(float $CAN): self
    {
        $this->CAN = $CAN;

        return $this;
    }

    public function getUSD(): ?float
    {
        return $this->USD;
    }

    public function setUSD(float $USD): self
    {
        $this->USD = $USD;

        return $this;
    }

    public function withdraw(string $currency, float $amount): self
    {
        if($currency === "CAN"){
            $this->CAN -= $amount;
        }
        
        if($currency === "USD"){
            $this->USD -= $amount;
        }
    
        return $this;
    }

    public function deposit(string $currency, float $amount): self
    {
        if($currency === "CAN"){
            $this->CAN += $amount;
        }
        
        if($currency === "USD"){
            $this->USD += $amount;
        }
    
        
        return $this;
    }
    
}
