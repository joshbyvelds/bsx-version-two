<?php

namespace App\Entity;

use App\Repository\OptionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OptionRepository::class)]
#[ORM\Table(name: '`option`')]
class Option
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Stock::class, inversedBy: 'options')]
    #[ORM\JoinColumn(nullable: false)]
    private $stock;

    #[ORM\Column(type: 'integer')]
    private $type;

    #[ORM\Column(type: 'float')]
    private $strike;

    #[ORM\Column(type: 'integer')]
    private $contracts;

    #[ORM\Column(type: 'float')]
    private $average;

    #[ORM\Column(type: 'integer')]
    private $buys;

    #[ORM\Column(type: 'date')]
    private $expiry;

    #[ORM\Column(type: 'date')]
    private $buy_date;

    #[ORM\Column(type: 'date')]
    private $sell_date;

    #[ORM\Column(type: 'float')]
    private $sell_price;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'options')]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\Column(type: 'boolean')]
    private $expired;

    #[ORM\Column(type: 'float')]
    private $current;

    #[ORM\Column(type: 'integer')]
    private $total_contracts;

    #[ORM\Column(type: 'integer')]
    private $total_contracts_sold;

    #[ORM\Column(type: 'integer')]
    private $sells;

    #[ORM\OneToMany(mappedBy: 'option_id', targetEntity: OptionRollover::class)]
    private $optionRollovers;

    public function __construct()
    {
        $this->optionRollovers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStock(): ?Stock
    {
        return $this->stock;
    }

    public function setStock(?Stock $stock): self
    {
        $this->stock = $stock;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getStrike(): ?float
    {
        return $this->strike;
    }

    public function setStrike(float $strike): self
    {
        $this->strike = $strike;

        return $this;
    }

    public function getContracts(): ?int
    {
        return $this->contracts;
    }

    public function setContracts(int $contracts): self
    {
        $this->contracts = $contracts;

        return $this;
    }

    public function getAverage(): ?float
    {
        return $this->average;
    }

    public function setAverage(float $average): self
    {
        $this->average = $average;

        return $this;
    }

    public function getBuys(): ?int
    {
        return $this->buys;
    }

    public function setBuys(int $buys): self
    {
        $this->buys = $buys;

        return $this;
    }

    public function getExpiry(): ?\DateTimeInterface
    {
        return $this->expiry;
    }

    public function setExpiry(\DateTimeInterface $expiry): self
    {
        $this->expiry = $expiry;

        return $this;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function isExpired(): ?bool
    {
        return $this->expired;
    }

    public function setExpired(bool $expired): self
    {
        $this->expired = $expired;

        return $this;
    }

    public function getCurrent(): ?float
    {
        return $this->current;
    }

    public function setCurrent(float $current): self
    {
        $this->current = $current;

        return $this;
    }

    public function getSellPrice(): ?float
    {
        return $this->sell_price;
    }

    public function setSellPrice(float $current): self
    {
        $this->sell_price = $current;

        return $this;
    }

    public function getBuyDate(): ?\DateTimeInterface
    {
        return $this->buy_date;
    }

    public function setBuyDate(\DateTimeInterface $date): self
    {
        $this->buy_date = $date;

        return $this;
    }

    public function getSellDate(): ?\DateTimeInterface
    {
        return $this->sell_date;
    }

    public function setSellDate(\DateTimeInterface $date): self
    {
        $this->sell_date = $date;

        return $this;
    }

    public function getTotalContracts(): ?int
    {
        return $this->total_contracts;
    }

    public function setTotalContracts(int $total_contracts): self
    {
        $this->total_contracts = $total_contracts;

        return $this;
    }

    public function getTotalContractsSold(): ?int
    {
        return $this->total_contracts_sold;
    }

    public function setTotalContractsSold(int $total_contracts_sold): self
    {
        $this->total_contracts_sold = $total_contracts_sold;

        return $this;
    }

    public function getSells(): ?int
    {
        return $this->sells;
    }

    public function setSells(int $sells): self
    {
        $this->sells = $sells;

        return $this;
    }

    /**
     * @return Collection<int, OptionRollover>
     */
    public function getOptionRollovers(): Collection
    {
        return $this->optionRollovers;
    }

    public function addOptionRollover(OptionRollover $optionRollover): self
    {
        if (!$this->optionRollovers->contains($optionRollover)) {
            $this->optionRollovers[] = $optionRollover;
            $optionRollover->setOptionId($this);
        }

        return $this;
    }

    public function removeOptionRollover(OptionRollover $optionRollover): self
    {
        if ($this->optionRollovers->removeElement($optionRollover)) {
            // set the owning side to null (unless already changed)
            if ($optionRollover->getOptionId() === $this) {
                $optionRollover->setOptionId(null);
            }
        }

        return $this;
    }
}
