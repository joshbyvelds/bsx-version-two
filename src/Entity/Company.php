<?php

namespace App\Entity;

use App\Repository\CompanyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompanyRepository::class)]
class Company
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\Column(type: 'string', length: 7)]
    private $ticker;

    #[ORM\Column(type: 'float')]
    private $current_price;

    #[ORM\Column(type: 'float')]
    private $price_yesterday;

    #[ORM\Column(type: 'float')]
    private $price_week;

    #[ORM\Column(type: 'float')]
    private $price_month;

    #[ORM\Column(type: 'float')]
    private $price_year;

    #[ORM\Column(type: 'boolean')]
    private $pays_dividend;

    #[ORM\ManyToOne(targetEntity: Sector::class, inversedBy: 'companies')]
    #[ORM\JoinColumn(nullable: false)]
    private $sector;

    #[ORM\Column(type: 'string', length: 10)]
    private $bgColor;

    #[ORM\OneToMany(mappedBy: 'company', targetEntity: Stock::class)]
    private $stocks;

    public function __construct()
    {
        $this->stocks = new ArrayCollection();
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

    public function getCurrentPrice(): ?float
    {
        return $this->current_price;
    }

    public function setCurrentPrice(float $current_price): self
    {
        $this->current_price = $current_price;

        return $this;
    }

    public function getPriceYesterday(): ?float
    {
        return $this->price_yesterday;
    }

    public function setPriceYesterday(float $price_yesterday): self
    {
        $this->price_yesterday = $price_yesterday;

        return $this;
    }

    public function getPriceWeek(): ?float
    {
        return $this->price_week;
    }

    public function setPriceWeek(float $price_week): self
    {
        $this->price_week = $price_week;

        return $this;
    }

    public function getPriceMonth(): ?float
    {
        return $this->price_month;
    }

    public function setPriceMonth(float $price_month): self
    {
        $this->price_month = $price_month;

        return $this;
    }

    public function getPriceYear(): ?float
    {
        return $this->price_year;
    }

    public function setPriceYear(float $price_year): self
    {
        $this->price_year = $price_year;

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

    public function getSector(): ?Sector
    {
        return $this->sector;
    }

    public function setSector(?Sector $sector): self
    {
        $this->sector = $sector;

        return $this;
    }

    public function getBgColor(): ?string
    {
        return $this->bgColor;
    }

    public function setBgColor(string $bgColor): self
    {
        $this->bgColor = $bgColor;

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
            $stock->setCompany($this);
        }

        return $this;
    }

    public function removeStock(Stock $stock): self
    {
        if ($this->stocks->removeElement($stock)) {
            // set the owning side to null (unless already changed)
            if ($stock->getCompany() === $this) {
                $stock->setCompany(null);
            }
        }

        return $this;
    }
}
