<?php

namespace App\Entity;

use App\Repository\WrittenOptionRolloverRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WrittenOptionRolloverRepository::class)]
class WrittenOptionRollover
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: WrittenOption::class, inversedBy: 'writtenOptionRollovers')]
    #[ORM\JoinColumn(nullable: false)]
    private $WrittenOption;

    #[ORM\Column(type: 'date')]
    private $OldExpiry;

    #[ORM\Column(type: 'date')]
    private $newExpiry;

    #[ORM\Column(type: 'float')]
    private $amount;

    #[ORM\Column(type: 'float')]
    private $stockRolloverPrice;

    #[ORM\Column(type: 'float')]
    private $oldStrike;

    #[ORM\Column(type: 'float')]
    private $newStrike;

    #[ORM\Column(type: 'float')]
    private $price;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWrittenOption(): ?WrittenOption
    {
        return $this->WrittenOption;
    }

    public function setWrittenOption(?WrittenOption $WrittenOption): self
    {
        $this->WrittenOption = $WrittenOption;

        return $this;
    }

    public function getOldExpiry(): ?\DateTimeInterface
    {
        return $this->OldExpiry;
    }

    public function setOldExpiry(\DateTimeInterface $OldExpiry): self
    {
        $this->OldExpiry = $OldExpiry;

        return $this;
    }

    public function getNewExpiry(): ?\DateTimeInterface
    {
        return $this->newExpiry;
    }

    public function setNewExpiry(\DateTimeInterface $newExpiry): self
    {
        $this->newExpiry = $newExpiry;

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

    public function getStockRolloverPrice(): ?float
    {
        return $this->stockRolloverPrice;
    }

    public function setStockRolloverPrice(float $stockRolloverPrice): self
    {
        $this->stockRolloverPrice = $stockRolloverPrice;

        return $this;
    }

    public function getOldStrike(): ?float
    {
        return $this->oldStrike;
    }

    public function setOldStrike(float $oldStrike): self
    {
        $this->oldStrike = $oldStrike;

        return $this;
    }

    public function getNewStrike(): ?float
    {
        return $this->newStrike;
    }

    public function setNewStrike(float $newStrike): self
    {
        $this->newStrike = $newStrike;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }
}
