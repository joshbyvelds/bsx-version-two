<?php

namespace App\Entity;

use App\Repository\TenPercentPlanWeekRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TenPercentPlanWeekRepository::class)]
class TenPercentPlanWeek
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'tenPercentPlanWeeks')]
    #[ORM\JoinColumn(nullable: false)]
    private $User;

    #[ORM\Column(type: 'integer')]
    private $week;

    #[ORM\Column(type: 'string', length: 60)]
    private $date;

    #[ORM\Column(type: 'float')]
    private $added;

    #[ORM\Column(type: 'float')]
    private $target;

    #[ORM\Column(type: 'float')]
    private $total;

    #[ORM\Column(type: 'float')]
    private $total_target;

    #[ORM\Column(type: 'float')]
    private $percent;

    #[ORM\Column(type: 'float')]
    private $next_week_target;

    #[ORM\Column(type: 'date')]
    private $week_ends;

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

    public function getWeek(): ?int
    {
        return $this->week;
    }

    public function setWeek(int $week): self
    {
        $this->week = $week;

        return $this;
    }

    public function getDate(): ?string
    {
        return $this->date;
    }

    public function setDate(string $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getAdded(): ?float
    {
        return $this->added;
    }

    public function setAdded(float $added): self
    {
        $this->added = $added;

        return $this;
    }

    public function getTarget(): ?float
    {
        return $this->target;
    }

    public function setTarget(float $target): self
    {
        $this->target = $target;

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

    public function getTotalTarget(): ?float
    {
        return $this->total_target;
    }

    public function setTotalTarget(float $total_target): self
    {
        $this->total_target = $total_target;

        return $this;
    }

    public function getPercent(): ?float
    {
        return $this->percent;
    }

    public function setPercent(float $percent): self
    {
        $this->percent = $percent;

        return $this;
    }

    public function getNextWeekTarget(): ?float
    {
        return $this->next_week_target;
    }

    public function setNextWeekTarget(float $next_week_target): self
    {
        $this->next_week_target = $next_week_target;

        return $this;
    }

    public function getWeekEnds(): ?\DateTimeInterface
    {
        return $this->week_ends;
    }

    public function setWeekEnds(\DateTimeInterface $week_ends): self
    {
        $this->week_ends = $week_ends;

        return $this;
    }
}
