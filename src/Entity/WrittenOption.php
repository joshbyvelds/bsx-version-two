<?php

namespace App\Entity;

use App\Repository\WrittenOptionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WrittenOptionRepository::class)]
class WrittenOption
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'coveredCalls')]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[ORM\ManyToOne(targetEntity: Stock::class, inversedBy: 'coveredCalls')]
    #[ORM\JoinColumn(nullable: false)]
    private $stock;

    #[ORM\Column(type: 'integer')]
    private $contracts;

    #[ORM\Column(type: 'integer')]
    private $contract_type;
    
    #[ORM\Column(type: 'float')]
    private $strike;

    #[ORM\Column(type: 'float')]
    private $price;

    #[ORM\Column(type: 'date')]
    private $expiry;

    #[ORM\Column(type: 'boolean')]
    private $expired;

    #[ORM\Column(type: 'boolean')]
    private $exercised;

    #[ORM\Column(type: 'float')]
    private $stock_buy_price;

    #[ORM\Column(type: 'float', nullable: true)]
    private $stock_expiry_price;

    #[ORM\Column(type: 'string', length: 3)]
    private $payment_currency;

    #[ORM\Column(type: 'boolean')]
    private $payment_locked;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $buyout;

    #[ORM\Column(type: 'float')]
    private $buyout_price;

    #[ORM\Column(type: 'float')]
    private $ask;

    #[ORM\OneToMany(mappedBy: 'WrittenOption', targetEntity: WrittenOptionRollover::class)]
    private $writtenOptionRollovers;

    public function __construct()
    {
        $this->writtenOptionRollovers = new ArrayCollection();
    }

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

    public function getStock(): ?Stock
    {
        return $this->stock;
    }

    public function setStock(?Stock $stock): self
    {
        $this->stock = $stock;

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

    public function getStrike(): ?float
    {
        return $this->strike;
    }

    public function setStrike(float $strike): self
    {
        $this->strike = $strike;

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

    public function getExpiry(): ?\DateTimeInterface
    {
        return $this->expiry;
    }

    public function setExpiry(\DateTimeInterface $expiry): self
    {
        $this->expiry = $expiry;

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

    public function isExercised(): ?bool
    {
        return $this->exercised;
    }

    public function setExercised(bool $exercised): self
    {
        $this->exercised = $exercised;

        return $this;
    }

    public function getStockBuyPrice(): ?float
    {
        return $this->stock_buy_price;
    }

    public function setStockBuyPrice(float $stock_buy_price): self
    {
        $this->stock_buy_price = $stock_buy_price;

        return $this;
    }

    public function getStockExpiryPrice(): ?float
    {
        return $this->stock_expiry_price;
    }

    public function setStockExpiryPrice(float $stock_expiry_price): self
    {
        $this->stock_expiry_price = $stock_expiry_price;

        return $this;
    }

    public function getContractType(): ?int
    {
        return $this->contract_type;
    }

    public function setContractType(float $contract_type): self
    {
        $this->contract_type = $contract_type;

        return $this;
    }

    public function getPaymentCurrency(): ?string
    {
        return $this->payment_currency;
    }

    public function setPaymentCurrency(string $payment_currency): self
    {
        $this->payment_currency = $payment_currency;

        return $this;
    }

    public function isPaymentLocked(): ?bool
    {
        return $this->payment_locked;
    }

    public function setPaymentLocked(bool $payment_locked): self
    {
        $this->payment_locked = $payment_locked;

        return $this;
    }

    public function isBuyout(): ?bool
    {
        return $this->buyout;
    }

    public function setBuyout(?bool $buyout): self
    {
        $this->buyout = $buyout;

        return $this;
    }

    public function getBuyoutPrice(): ?float
    {
        return $this->buyout_price;
    }

    public function setBuyoutPrice(float $buyoutPrice): self
    {
        $this->buyout_price = $buyoutPrice;

        return $this;
    }

    public function getAsk(): ?float
    {
        return $this->ask;
    }

    public function setAsk(float $ask): self
    {
        $this->ask = $ask;

        return $this;
    }

    /**
     * @return Collection<int, WrittenOptionRollover>
     */
    public function getWrittenOptionRollovers(): Collection
    {
        return $this->writtenOptionRollovers;
    }

    public function addWrittenOptionRollover(WrittenOptionRollover $writtenOptionRollover): self
    {
        if (!$this->writtenOptionRollovers->contains($writtenOptionRollover)) {
            $this->writtenOptionRollovers[] = $writtenOptionRollover;
            $writtenOptionRollover->setWrittenOption($this);
        }

        return $this;
    }

    public function removeWrittenOptionRollover(WrittenOptionRollover $writtenOptionRollover): self
    {
        if ($this->writtenOptionRollovers->removeElement($writtenOptionRollover)) {
            // set the owning side to null (unless already changed)
            if ($writtenOptionRollover->getWrittenOption() === $this) {
                $writtenOptionRollover->setWrittenOption(null);
            }
        }

        return $this;
    }


}
