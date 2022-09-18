<?php

namespace App\Entity;

use App\Repository\FuturesBucketsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FuturesBucketsRepository::class)]
class FuturesBuckets
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'futuresBuckets')]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[ORM\Column(type: 'float')]
    private $play;

    #[ORM\Column(type: 'float')]
    private $profit;

    #[ORM\Column(type: 'float')]
    private $withdrawn;

    #[ORM\Column(type: 'float')]
    private $used;

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

    public function getPlay(): ?float
    {
        return $this->play;
    }

    public function setPlay(float $play): self
    {
        $this->play = $play;

        return $this;
    }

    public function getProfit(): ?float
    {
        return $this->profit;
    }

    public function setProfit(float $profit): self
    {
        $this->profit = $profit;

        return $this;
    }

    public function AddProfit(float $profit): self
    {
        $this->profit += $profit;

        return $this;
    }

    public function getWithdrawn(): ?float
    {
        return $this->withdrawn;
    }

    public function setWithdrawn(float $withdrawn): self
    {
        $this->withdrawn = $withdrawn;

        return $this;
    }

    public function getUsed(): ?float
    {
        return $this->used;
    }

    public function setUsed(float $used): self
    {
        $this->used = $used;

        return $this;
    }

    public function lostPlayMoney(float $moneyLost): self
    {
        $this->play -= $moneyLost;

        return $this;
    }

    public function dumpProfitBucket($cdn): self
    {
        $this->setWithdrawn($this->getWithdrawn() + ($cdn - 30));
        $this->setProfit(0);
        return $this;
    }
}
