<?php

namespace App\Entity;

use App\Repository\StockRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StockRepository::class)]
class Stock
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\Column(type: 'string', length: 10)]
    private $ticker;

    #[ORM\Column(type: 'float')]
    private $earned;

    #[ORM\Column(type: 'date', nullable: true)]
    private $last_bought;

    #[ORM\Column(type: 'date', nullable: true)]
    private $last_sold;

    #[ORM\Column(type: 'string', length: 3)]
    private $country;

    #[ORM\Column(type: 'string', length: 12)]
    private $type;

    #[ORM\OneToMany(mappedBy: 'Stock', targetEntity: Dividend::class)]
    private $dividends;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'stocks')]
    #[ORM\JoinColumn(nullable: false)]
    private $user;
    
    #[ORM\OneToMany(mappedBy: 'stock', targetEntity: ShareBuy::class, orphanRemoval: true)]
    private $shareBuys;

    #[ORM\Column(type: 'float')]
    private $current_price;

    #[ORM\Column(type: 'datetime')]
    private $last_price_update;

    #[ORM\Column(type: 'string', length: 6)]
    private $bg_color;

    #[ORM\OneToMany(mappedBy: 'stock', targetEntity: ShareSell::class, orphanRemoval: true)]
    private $shareSells;

    #[ORM\Column(type: 'boolean')]
    private $pays_dividend;

    #[ORM\Column(type: 'boolean')]
    private $delisted;

    #[ORM\OneToMany(mappedBy: 'stock', targetEntity: Option::class)]
    private $options;

    #[ORM\Column(type: 'boolean')]
    private $beingPlayedOption;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $beingPlayedShares;

    #[ORM\OneToMany(mappedBy: 'stock', targetEntity: WrittenOption::class)]
    private $coveredCalls;

    #[ORM\Column(type: 'integer')]
    private $sharesOwned;

    #[ORM\Column(type: 'boolean')]
    private $no_fee;

    public function __construct()
    {
        $this->dividends = new ArrayCollection();
        $this->shareBuys = new ArrayCollection();
        $this->shareSells = new ArrayCollection();
        $this->options = new ArrayCollection();
        $this->coveredCalls = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getTicker(): ?string
    {
        return $this->ticker;
    }

    public function setTicker(string $ticker): self
    {
        $this->ticker = $ticker;

        return $this;
    }

    public function getEarned(): ?float
    {
        return $this->earned;
    }

    public function setEarned(float $earned): self
    {
        $this->earned = $earned;

        return $this;
    }

    public function subtractEarned(float $amount): self
    {
        $this->earned -= $amount;
        return $this;
    }

    public function addEarned(float $amount): self
    {
        $this->earned += $amount;
        return $this;
    }

    public function getLastBought(): ?\DateTimeInterface
    {
        return $this->last_bought;
    }

    public function setLastBought(\DateTimeInterface $last_bought): self
    {
        $this->last_bought = $last_bought;

        return $this;
    }

    public function getLastSold(): ?\DateTimeInterface
    {
        return $this->last_sold;
    }

    public function setLastSold(?\DateTimeInterface $last_sold): self
    {
        $this->last_sold = $last_sold;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection<int, Dividend>
     */
    public function getDividends(): Collection
    {
        return $this->dividends;
    }

    public function addDividend(Dividend $dividend): self
    {
        if (!$this->dividends->contains($dividend)) {
            $this->dividends[] = $dividend;
            $dividend->setStock($this);
        }

        return $this;
    }

    public function removeDividend(Dividend $dividend): self
    {
        if ($this->dividends->removeElement($dividend)) {
            // set the owning side to null (unless already changed)
            if ($dividend->getStock() === $this) {
                $dividend->setStock(null);
            }
        }

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

    /**
     * @return Collection<int, ShareBuy>
     */
    public function getShareBuys(): Collection
    {
        return $this->shareBuys;
    }

    public function addShareBuy(ShareBuy $shareBuy): self
    {
        if (!$this->shareBuys->contains($shareBuy)) {
            $this->shareBuys[] = $shareBuy;
            $shareBuy->setStock($this);
        }

        return $this;
    }

    public function removeShareBuy(ShareBuy $shareBuy): self
    {
        if ($this->shareBuys->removeElement($shareBuy)) {
            // set the owning side to null (unless already changed)
            if ($shareBuy->getStock() === $this) {
                $shareBuy->setStock(null);
            }
        }

        return $this;
    }

    public function getCurrentPrice(): ?float
    {
        return $this->current_price;
    }

    public function setCurrentPrice(float $current_price): self
    {
        $this->current_price = $current_price;

        return $this;
    }

    public function getLastPriceUpdate(): ?\DateTimeInterface
    {
        return $this->last_price_update;
    }

    public function setLastPriceUpdate(\DateTimeInterface $last_price_update): self
    {
        $this->last_price_update = $last_price_update;

        return $this;
    }

    public function getBgColor(): ?string
    {
        return $this->bg_color;
    }

    public function setBgColor(string $bg_color): self
    {
        $this->bg_color = $bg_color;

        return $this;
    }

    /**
     * @return Collection<int, ShareSell>
     */
    public function getShareSells(): Collection
    {
        return $this->shareSells;
    }

    public function addShareSell(ShareSell $shareSell): self
    {
        if (!$this->shareSells->contains($shareSell)) {
            $this->shareSells[] = $shareSell;
            $shareSell->setStock($this);
        }

        return $this;
    }

    public function removeShareSell(ShareSell $shareSell): self
    {
        if ($this->shareSells->removeElement($shareSell)) {
            // set the owning side to null (unless already changed)
            if ($shareSell->getStock() === $this) {
                $shareSell->setStock(null);
            }
        }

        return $this;
    }

    public function isPaysDividend(): ?bool
    {
        return $this->pays_dividend;
    }

    public function setPaysDividend(bool $pays_dividend): self
    {
        $this->pays_dividend = $pays_dividend;

        return $this;
    }

    public function isDelisted(): ?bool
    {
        return $this->delisted;
    }

    public function setDelisted(bool $delisted): self
    {
        $this->delisted = $delisted;

        return $this;
    }

    /**
     * @return Collection<int, Option>
     */
    public function getOptions(): Collection
    {
        return $this->options;
    }

    public function addOption(Option $option): self
    {
        if (!$this->options->contains($option)) {
            $this->options[] = $option;
            $option->setStock($this);
        }

        return $this;
    }

    public function removeOption(Option $option): self
    {
        if ($this->options->removeElement($option)) {
            // set the owning side to null (unless already changed)
            if ($option->getStock() === $this) {
                $option->setStock(null);
            }
        }

        return $this;
    }

    public function isBeingPlayedOption(): ?bool
    {
        return $this->beingPlayedOption;
    }

    public function setBeingPlayedOption(bool $beingPlayedOption): self
    {
        $this->beingPlayedOption = $beingPlayedOption;

        return $this;
    }

    public function isBeingPlayedShares(): ?bool
    {
        return $this->beingPlayedShares;
    }

    public function setBeingPlayedShares(bool $beingPlayedShares): self
    {
        $this->$beingPlayedShares = $beingPlayedShares;

        return $this;
    }

    /**
     * @return Collection<int, WrittenOption>
     */
    public function getCoveredCalls(): Collection
    {
        return $this->coveredCalls;
    }

    public function addCoveredCall(WrittenOption $coveredCall): self
    {
        if (!$this->coveredCalls->contains($coveredCall)) {
            $this->coveredCalls[] = $coveredCall;
            $coveredCall->setStock($this);
        }

        return $this;
    }

    public function removeCoveredCall(WrittenOption $coveredCall): self
    {
        if ($this->coveredCalls->removeElement($coveredCall)) {
            // set the owning side to null (unless already changed)
            if ($coveredCall->getStock() === $this) {
                $coveredCall->setStock(null);
            }
        }

        return $this;
    }

    public function getSharesOwned(): ?int
    {
        return $this->sharesOwned;
    }

    public function addSharesOwned(int $newShares): self
    {
        $this->sharesOwned += $newShares;

        return $this;
    }

    public function setSharesOwned(int $sharesOwned): self
    {
        $this->sharesOwned = $sharesOwned;

        return $this;
    }

    public function isNoFee(): ?bool
    {
        return $this->no_fee;
    }

    public function setNoFee(bool $no_fee): self
    {
        $this->no_fee = $no_fee;

        return $this;
    }
}
