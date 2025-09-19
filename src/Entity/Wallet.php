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

    #[ORM\Column(type: 'float')]
    private $locked_cdn;

    #[ORM\Column(type: 'float')]
    private $locked_usd;

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

    public function getLockedCdn(): ?float
    {
        return $this->locked_cdn;
    }

    public function setLockedCdn(float $locked_cdn): self
    {
        $this->locked_cdn = $locked_cdn;

        return $this;
    }

    public function getLockedUsd(): ?float
    {
        return $this->locked_usd;
    }

    public function setLockedUsd(float $locked_usd): self
    {
        $this->locked_usd = $locked_usd;

        return $this;
    }

    public function lock(string $currency, float $amount): self
    {
        if($currency === "CAN"){
            $this->locked_cdn += $amount;
        }
        
        if($currency === "USD"){
            $this->locked_usd += $amount;
        }
    
        return $this;
    }

    public function transfer(string $currency, string $type, float $amount): self
    {
        if($currency === "CAN"){
            if ($type === "unlock"){
                $this->locked_cdn -= $amount;
                $this->CAN += $amount;
            }

            if ($type === "lock"){
                $this->locked_cdn += $amount;
                $this->CAN -= $amount;
            }
        }

        if ($currency === "USD"){
            if ($type === "unlock"){
                $this->locked_usd -= $amount;
                $this->USD += $amount;
            }

            if ($type === "lock"){
                $this->locked_usd += $amount;
                $this->USD -= $amount;
            }
        }

        return $this;
    }

    public function unlock(string $currency, float $amount): self
    {
        if($currency === "CAN"){
            $amount_to_unlock = ($amount < $this->locked_cdn) ? $amount : $this->locked_cdn;
            $this->locked_cdn -= $amount_to_unlock;
            $this->CAN += $amount_to_unlock;
        }
        
        if($currency === "USD"){
            $amount_to_unlock = ($amount < $this->locked_usd) ? $amount : $this->locked_usd;
            $this->locked_usd -= $amount_to_unlock;
            $this->USD += $amount_to_unlock;
        }
    
        return $this;
    }
    
}
