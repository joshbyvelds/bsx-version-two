<?php

namespace App\Entity;

use App\Repository\FuturesWeekRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FuturesWeekRepository::class)]
class FuturesWeek
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'futuresWeeks')]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[ORM\Column(type: 'integer')]
    private $trades;

    #[ORM\Column(type: 'float')]
    private $pl;

    #[ORM\Column(type: 'float')]
    private $fees;

    #[ORM\Column(type: 'float')]
    private $play;

    #[ORM\Column(type: 'float')]
    private $profit;

    #[ORM\Column(type: 'date')]
    private $start;

    #[ORM\Column(type: 'date')]
    private $end;

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

    public function getTrades(): ?int
    {
        return $this->trades;
    }

    public function setTrades(int $trades): self
    {
        $this->trades = $trades;

        return $this;
    }

    public function getPl(): ?float
    {
        return $this->pl;
    }

    public function setPl(float $pl): self
    {
        $this->pl = $pl;

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

    public function getStart(): ?\DateTimeInterface
    {
        return $this->start;
    }

    public function setStart(\DateTimeInterface $start): self
    {
        $this->start = $start;

        return $this;
    }

    public function getEnd(): ?\DateTimeInterface
    {
        return $this->end;
    }

    public function setEnd(\DateTimeInterface $end): self
    {
        $this->end = $end;

        return $this;
    }
}
