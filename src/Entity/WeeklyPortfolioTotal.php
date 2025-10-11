<?php

namespace App\Entity;

use App\Repository\WeeklyPortfolioTotalRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WeeklyPortfolioTotalRepository::class)]
class WeeklyPortfolioTotal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'weeklyPortfolioTotals')]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[ORM\Column(type: 'date')]
    private $startDate;

    #[ORM\Column(type: 'date')]
    private $endDate;

    #[ORM\Column(type: 'float')]
    private $amount;

    #[ORM\Column(type: 'boolean')]
    private $current;

    #[ORM\Column(type: 'boolean')]
    private $endofmonth;

    #[ORM\Column(type: 'boolean')]
    private $endofyear;

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

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

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

    public function isCurrent(): ?bool
    {
        return $this->current;
    }

    public function setCurrent(bool $current): self
    {
        $this->current = $current;

        return $this;
    }

    public function isEndofmonth(): ?bool
    {
        return $this->endofmonth;
    }

    public function setEndofmonth(bool $endofmonth): self
    {
        $this->endofmonth = $endofmonth;

        return $this;
    }

    public function isEndofyear(): ?bool
    {
        return $this->endofyear;
    }

    public function setEndofyear(bool $endofyear): self
    {
        $this->endofyear = $endofyear;

        return $this;
    }
}
