<?php

namespace App\Entity;

use App\Repository\PortfolioRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PortfolioRepository::class)]
class Portfolio
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'portfolios')]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[ORM\ManyToMany(targetEntity: Stock::class)]
    private $stocks;

    #[ORM\ManyToMany(targetEntity: ShareBuy::class)]
    private $ShareBuys;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\Column(type: 'datetime')]
    private $updated;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $largeType;

    #[ORM\Column(type: 'float')]
    private $worth;

    #[ORM\Column(type: 'float')]
    private $yesterday;

    #[ORM\Column(type: 'float')]
    private $lastWeek;

    #[ORM\Column(type: 'float')]
    private $lastMonth;

    #[ORM\Column(type: 'float')]
    private $lastYear;

    public function __construct()
    {
        $this->stocks = new ArrayCollection();
        $this->ShareBuys = new ArrayCollection();
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

    /**
     * @return Collection<int, Stock>
     */
    public function getStocks(): Collection
    {
        return $this->stocks;
    }

    public function addStock(Stock $stock): self
    {
        if (!$this->stocks->contains($stock)) {
            $this->stocks[] = $stock;
        }

        return $this;
    }

    public function removeStock(Stock $stock): self
    {
        $this->stocks->removeElement($stock);

        return $this;
    }

    /**
     * @return Collection<int, ShareBuy>
     */
    public function getShareBuys(): Collection
    {
        return $this->ShareBuys;
    }

    public function addShareBuy(ShareBuy $shareBuy): self
    {
        if (!$this->ShareBuys->contains($shareBuy)) {
            $this->ShareBuys[] = $shareBuy;
        }

        return $this;
    }

    public function removeShareBuy(ShareBuy $shareBuy): self
    {
        $this->ShareBuys->removeElement($shareBuy);

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

    public function getUpdated(): ?\DateTimeInterface
    {
        return $this->updated;
    }

    public function setUpdated(\DateTimeInterface $updated): self
    {
        $this->updated = $updated;

        return $this;
    }

    public function isLargeType(): ?bool
    {
        return $this->largeType;
    }

    public function setLargeType(?bool $largeType): self
    {
        $this->largeType = $largeType;

        return $this;
    }

    public function getWorth(): ?float
    {
        return $this->worth;
    }

    public function setWorth(float $worth): self
    {
        $this->worth = $worth;

        return $this;
    }

    public function getYesterday(): ?float
    {
        return $this->yesterday;
    }

    public function setYesterday(float $yesterday): self
    {
        $this->yesterday = $yesterday;

        return $this;
    }

    public function getLastWeek(): ?float
    {
        return $this->lastWeek;
    }

    public function setLastWeek(float $lastWeek): self
    {
        $this->lastWeek = $lastWeek;

        return $this;
    }

    public function getLastMonth(): ?float
    {
        return $this->lastMonth;
    }

    public function setLastMonth(float $lastMonth): self
    {
        $this->lastMonth = $lastMonth;

        return $this;
    }

    public function getLastYear(): ?float
    {
        return $this->lastYear;
    }

    public function setLastYear(float $lastYear): self
    {
        $this->lastYear = $lastYear;

        return $this;
    }
}
