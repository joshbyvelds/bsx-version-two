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

    #[ORM\OneToMany(mappedBy: 'Stock', targetEntity: Dividend::class)]
    private $dividends;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'stocks')]
    #[ORM\JoinColumn(nullable: false)]
    private $user;
    
    #[ORM\OneToMany(mappedBy: 'stock', targetEntity: ShareBuy::class, orphanRemoval: true)]
    private $shareBuys;

    #[ORM\OneToMany(mappedBy: 'stock', targetEntity: Play::class, orphanRemoval: true)]
    private $plays;

    #[ORM\OneToMany(mappedBy: 'stock', targetEntity: ShareSell::class, orphanRemoval: true)]
    private $shareSells;

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

    #[ORM\ManyToOne(targetEntity: Company::class, inversedBy: 'stocks')]
    #[ORM\JoinColumn(nullable: false)]
    private $company;

    public function __construct()
    {
        $this->dividends = new ArrayCollection();
        $this->shareBuys = new ArrayCollection();
        $this->shareSells = new ArrayCollection();
        $this->options = new ArrayCollection();
        $this->coveredCalls = new ArrayCollection();
        $this->plays = new ArrayCollection();
    }

    // Helper Functions..

    public function getCoveredCallDebt(): float{
        $cc_total = $this->getCoveredCallTotal();
        if($cc_total < 0 ) {
            return $cc_total * -1;
        }
        return 0.0;
    }

    public function getCoveredCallEarned(): float{
        $cc_total = $this->getCoveredCallTotal();
        if($cc_total > 0 ) {
            return $cc_total;
        }
        return 0.0;
    }

    public function getCoveredCallTotal(): float{
        $cc_total = 0.0;
        forEach($this->coveredCalls as $cc){
            $cc_total += ((($cc->getPrice() * $cc->getContracts()) * 100) - 9.95 - (1.25 * $cc->getContracts())) - $cc->getBuyoutPrice();
        }

        return $cc_total;
    }

    public function getOptionsDebt(): float{
        $options_total = $this->getOptionsTotal();
        if($options_total < 0 ) {
            return $options_total * -1;
        }
        return 0.0;
    }

    public function getOptionsEarned(): float{
        $options_total = $this->getOptionsTotal();
        if($options_total > 0 ) {
            return $options_total;
        }
        return 0.0;
    }

    public function getOptionsTotal(): float
    {
        $options_total = 0;
        $options_cost = 0;
        $options_rollover = 0;
        foreach ($this->options as $option) {
            $options_rollover = 0;
            foreach ($option->getOptionRollovers() as $rollover) {
                $options_rollover += (((($rollover->getOldPrice() - $rollover->getNewPrice()) * $rollover->getContracts()) * 100) - 9.95 - (1.25 * $rollover->getContracts()));
                $options_cost += (9.95 + (1.25 * $rollover->getContracts()));
            }

            $option_fees = (9.95 * ($option->getBuys() + $option->getSells())) + (1.25 * $option->getTotalContracts()) + (1.25 * $option->getTotalContractsSold());
            $options_total = $options_total - $option_fees + (($option->getTotalContractsSold() * ($option->getSellPrice() * 100)) - ($option->getTotalContracts() * ($option->getAverage() * 100))) + $options_rollover;
            $options_cost += ($option->getTotalContracts() * ($option->getAverage() * 100));
        }
        return $options_total;
    }

    public function getActiveOptionsValue(): float
    {
        $total = 0.0;
        foreach ($this->options as $option) {
            if (!$option->isExpired()) {
                $total += ($option->getContracts() * ($option->getCurrent() * 100)) - ($option->getContracts() * ($option->getAverage() * 100)) - (9.95 - (1.25 * $option->getContracts()) * 2);
            }
        }
        return $total;
    }

    function getDividendTotal(): float {
        $total = 0.0;
        foreach ($this->getDividends() as $dividend_payment) {
            $total += $dividend_payment->getAmount();
        }
        return $total;
    }

    function getTotalShareBuys(): int {
        $total = 0;
        foreach ($this->shareBuys as $buy) {
            if (!$this->getCompany()->isNoFee() && !$buy->isNoFee()) {
                $total += 1;
            }
        }

        return $total;
    }

    function getShareBuyCostTotal(): float {
        $total = 0.0;
        foreach ($this->shareBuys as $buy) {
            if ($this->getCompany()->isNoFee() || $buy->isNoFee()) {
                $total += ($buy->getPrice() * $buy->getAmount());
            } else {
                $total += ($buy->getPrice() * $buy->getAmount()) + 9.95;
            }
        }

        return $total;
    }

    function getTotalShareSells(): int {
        return count($this->getShareSells());
    }

    function getSharesSoldTotal(): float {
        $total = 0.0;

        foreach ($this->getShareSells() as $sell) {
            if ($this->getCompany()->isNoFee() || $sell->isNoFee()) {
                $total += ($sell->getPrice() * $sell->getAmount());
            } else {
                $total += ($sell->getPrice() * $sell->getAmount()) + 9.95;
            }
        }

        return $total;
    }

    public function getTotalDebt(): float{
        return $this->getShareBuyCostTotal() + $this->getCoveredCallDebt() + $this->getOptionsDebt();
    }

    public function getShareValue(): float{
        return ($this->getSharesOwned() * $this->getCompany()->getCurrentPrice()) - 9.95;
    }

    public function getTotalReturned(): float {
        return $this->getSharesSoldTotal() + $this->getDividendTotal() + $this->getCoveredCallEarned() + $this->getOptionsEarned();

        // {% set total_returned = total_sold + dividend_total + cc_earned + options_earned %}
    }

    function getPositionValue():float {
        return $this->getShareValue() + $this->getActiveOptionsValue();
    }

    function getTotalValue(): float {
        return $this->getTotalReturned() + $this->getPositionValue();
    }

    public function getRemainingTillProfitable(): float{
        return $this->getTotalReturned() - $this->getTotalDebt();
    }

    public function getRemainingTillProfitableAbs(): float{
        return $this->getRemainingTillGold() * -1;
    }

    public function getRemainingTillProfitableIfCurrentSharesAreSold(): float{
        $totalValue = $this->getTotalReturned() + $this->getShareValue();
        return $totalValue - $this->getTotalDebt();
    }

    public function getRemainingTillProfitableIfCurrentSharesAreSoldAbs(): float{
        return $this->getRemainingTillProfitableIfCurrentSharesAreSold() * -1;
    }

    public function getRemainingTillProfitableIfCurrentPositionsAreSold(): float{
        $totalValue = $this->getTotalValue();
        return $totalValue - $this->getTotalDebt();
    }

    public function getRemainingTillProfitableIfCurrentPositionsAreSoldAbs(): float{
        return $this->getRemainingTillProfitableIfCurrentPositionsAreSold() * -1;
    }


    public function getPercentIncrease(): float{
        return ((($this->getTotalReturned() - $this->getTotalDebt()) / $this->getTotalDebt()) * 100);
    }

    public function getPercentIncreaseWithShareValue(): float{
        $totalValue = $this->getTotalReturned() + $this->getShareValue();
        return ((($totalValue - $this->getTotalDebt()) / $this->getTotalDebt()) * 100);
    }

    public function getPercentIncreaseWithPositionValue(): float{
        $totalValue = $this->getTotalReturned() + $this->getShareValue() + $this->getActiveOptionsValue();
        return ((($totalValue - $this->getTotalDebt()) / $this->getTotalDebt()) * 100);
    }

    public function isProfitable(): bool {
        return ($this->getPercentIncrease() > 0);
    }

    public function isGoldStatus(): bool {
        return ($this->getPercentIncrease() >= 100);
    }

    // Property Getter/Setters

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

    /**
     * @return Collection<int, Play>
     */
    public function getPlays(): Collection
    {
        return $this->plays;
    }

    public function addPlay(Play $play): self
    {
        if (!$this->plays->contains($play)) {
            $this->plays[] = $play;
            $play->setStock($this);
        }

        return $this;
    }

    public function removePlay(Play $play): self
    {
        if ($this->plays->removeElement($play)) {
            // set the owning side to null (unless already changed)
            if ($play->getStock() === $this) {
                $play->setStock(null);
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
        $total = 0;

        foreach ($this->shareBuys as $buy) {
            if ($buy->getSold() < $buy->getAmount()){
                $remaining = $buy->getAmount() - $buy->getSold();
                $total += $remaining;
            }
        }

        return $total;
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

    public function getSector(): ?Sector
    {
        return $this->sector;
    }

    public function setSector(?Sector $sector): self
    {
        $this->sector = $sector;

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): self
    {
        $this->company = $company;

        return $this;
    }
}
