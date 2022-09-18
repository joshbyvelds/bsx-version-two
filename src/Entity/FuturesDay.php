<?php

namespace App\Entity;

use App\Repository\FuturesDayRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FuturesDayRepository::class)]
class FuturesDay
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'futuresDays')]
    #[ORM\JoinColumn(nullable: false)]
    private $User;

    #[ORM\Column(type: 'date')]
    private $date;

    #[ORM\Column(type: 'integer')]
    private $trades;

    #[ORM\Column(type: 'float')]
    private $amount;

    #[ORM\Column(type: 'float')]
    private $fees;

    #[ORM\Column(type: 'float')]
    private $total;

    #[ORM\Column(type: 'float')]
    private $play;

    #[ORM\Column(type: 'float')]
    private $profit;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->User;
    }

    public function setUser(?User $User): self
    {
        $this->User = $User;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getTrades(): ?int
    {
        return $this->trades;
    }

    public function setTrades(int $trades): self
    {
        $this->trades = $trades;

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

    public function getFees(): ?float
    {
        return $this->fees;
    }

    public function setFees(float $fees): self
    {
        $this->fees = $fees;

        return $this;
    }

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(float $total): self
    {
        $this->total = $total;

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
}
