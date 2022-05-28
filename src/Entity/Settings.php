<?php

namespace App\Entity;

use App\Repository\SettingsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SettingsRepository::class)]
class Settings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer')]
    private $dashboard_transactions;

    #[ORM\OneToOne(inversedBy: 'settings', targetEntity: User::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private $User;

    #[ORM\Column(type: 'integer')]
    private $max_play_money;

    #[ORM\Column(type: 'integer')]
    private $max_plays;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDashboardTransactions(): ?int
    {
        return $this->dashboard_transactions;
    }

    public function setDashboardTransactions(int $dashboard_transactions): self
    {
        $this->dashboard_transactions = $dashboard_transactions;

        return $this;
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

    public function getMaxPlayMoney(): ?int
    {
        return $this->max_play_money;
    }

    public function setMaxPlayMoney(int $max_play_money): self
    {
        $this->max_play_money = $max_play_money;

        return $this;
    }

    public function getMaxPlays(): ?int
    {
        return $this->max_plays;
    }

    public function setMaxPlays(int $max_plays): self
    {
        $this->max_plays = $max_plays;

        return $this;
    }
}
