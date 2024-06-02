<?php

namespace App\Entity;

use App\Repository\TFSAContributionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TFSAContributionRepository::class)]
class TFSAContribution
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'TFSAContributions')]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[ORM\Column(type: 'date')]
    private $date;

    #[ORM\Column(type: 'string', length: 255)]
    private $title;

    #[ORM\Column(type: 'float')]
    private $deposit;

    #[ORM\Column(type: 'float')]
    private $withdrawal;

    #[ORM\Column(type: 'float')]
    private $interest;

    #[ORM\Column(type: 'text', nullable: true)]
    private $note;

    #[ORM\Column(type: 'float')]
    private $locked;

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

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDeposit(): ?float
    {
        return $this->deposit;
    }

    public function setDeposit(float $deposit): self
    {
        $this->deposit = $deposit;

        return $this;
    }

    public function getWithdrawal(): ?float
    {
        return $this->withdrawal;
    }

    public function setWithdrawal(float $withdrawal): self
    {
        $this->withdrawal = $withdrawal;

        return $this;
    }

    public function getInterest(): ?float
    {
        return $this->interest;
    }

    public function setInterest(float $interest): self
    {
        $this->interest = $interest;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): self
    {
        $this->note = $note;

        return $this;
    }

    public function getLocked(): ?float
    {
        return $this->locked;
    }

    public function setLocked(float $locked): self
    {
        $this->locked = $locked;

        return $this;
    }
}
