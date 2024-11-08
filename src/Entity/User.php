<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @UniqueEntity(fields={"username"}, message="There is already an account with this username")
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private $username;

    #[ORM\Column(type: 'string', length: 40)]
    private $realname;

    #[ORM\Column(type: 'json')]
    private $roles = [];

    #[ORM\Column(type: 'string')]
    private $password;

    #[ORM\OneToOne(mappedBy: 'User', targetEntity: Wallet::class, cascade: ['persist', 'remove'])]
    private $wallet;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Transaction::class, orphanRemoval: true)]
    private $transactions;

    #[ORM\OneToOne(mappedBy: 'User', targetEntity: Settings::class, cascade: ['persist', 'remove'])]
    private $settings;

    #[ORM\OneToMany(mappedBy: 'User', targetEntity: TenPercentPlanWeek::class, orphanRemoval: true)]
    private $tenPercentPlanWeeks;

    #[ORM\Column(type: 'date', nullable: true)]
    private $ten_percent_start_date;

    #[ORM\Column(type: 'float', nullable: true)]
    private $ten_percent_start_amount;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Stock::class, orphanRemoval: true)]
    private $stocks;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Dividend::class)]
    private $dividends;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: FuturesBuckets::class, orphanRemoval: true)]
    private $futuresBuckets;

    #[ORM\OneToMany(mappedBy: 'User', targetEntity: FuturesDay::class, orphanRemoval: true)]
    private $futuresDays;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: FuturesWeek::class, orphanRemoval: true)]
    private $futuresWeeks;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Option::class)]
    private $options;

    #[ORM\OneToMany(mappedBy: 'User', targetEntity: Play::class)]
    private $plays;

    #[ORM\OneToMany(mappedBy: 'User', targetEntity: Note::class)]
    private $notes;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: WrittenOption::class)]
    private $writtenOptions;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Portfolio::class, orphanRemoval: true)]
    private $portfolios;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: TFSAContribution::class)]
    private $TFSAContributions;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: FHSAContribution::class)]
    private $FHSAContributions;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: RRSPContribution::class)]
    private $RRSPContributions;

    #[ORM\OneToMany(mappedBy: 'User', targetEntity: TotalValue::class)]
    private $totalValues;

    #[ORM\OneToMany(mappedBy: 'User', targetEntity: Debt::class)]
    private $debts;

    public function __construct()
    {
        $this->transactions = new ArrayCollection();
        $this->tenPercentPlanWeeks = new ArrayCollection();
        $this->stocks = new ArrayCollection();
        $this->dividends = new ArrayCollection();
        $this->futuresBuckets = new ArrayCollection();
        $this->futuresDays = new ArrayCollection();
        $this->futuresWeeks = new ArrayCollection();
        $this->options = new ArrayCollection();
        $this->plays = new ArrayCollection();
        $this->notes = new ArrayCollection();
        $this->writtenOptions = new ArrayCollection();
        $this->portfolios = new ArrayCollection();
        $this->TFSAContributions = new ArrayCollection();
        $this->FHSAContributions = new ArrayCollection();
        $this->RRSPContributions = new ArrayCollection();
        $this->totalValues = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getRealname(): ?string
    {
        return $this->realname;
    }

    public function setRealname(string $realname): self
    {
        $this->realname = $realname;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getWallet(): ?Wallet
    {
        return $this->wallet;
    }

    public function setWallet(Wallet $wallet): self
    {
        // set the owning side of the relation if necessary
        if ($wallet->getUser() !== $this) {
            $wallet->setUser($this);
        }

        $this->wallet = $wallet;

        return $this;
    }

    /**
     * @return Collection|Transaction[]
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(Transaction $transaction): self
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions[] = $transaction;
            $transaction->setUser($this);
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): self
    {
        if ($this->transactions->removeElement($transaction)) {
            // set the owning side to null (unless already changed)
            if ($transaction->getUser() === $this) {
                $transaction->setUser(null);
            }
        }

        return $this;
    }

    public function getSettings(): ?Settings
    {
        return $this->settings;
    }

    public function setSettings(Settings $settings): self
    {
        // set the owning side of the relation if necessary
        if ($settings->getUser() !== $this) {
            $settings->setUser($this);
        }

        $this->settings = $settings;

        return $this;
    }

    /**
     * @return Collection<int, TenPercentPlanWeek>
     */
    public function getTenPercentPlanWeeks(): Collection
    {
        return $this->tenPercentPlanWeeks;
    }

    public function addTenPercentPlanWeek(TenPercentPlanWeek $tenPercentPlanWeek): self
    {
        if (!$this->tenPercentPlanWeeks->contains($tenPercentPlanWeek)) {
            $this->tenPercentPlanWeeks[] = $tenPercentPlanWeek;
            $tenPercentPlanWeek->setUser($this);
        }

        return $this;
    }

    public function removeTenPercentPlanWeek(TenPercentPlanWeek $tenPercentPlanWeek): self
    {
        if ($this->tenPercentPlanWeeks->removeElement($tenPercentPlanWeek)) {
            // set the owning side to null (unless already changed)
            if ($tenPercentPlanWeek->getUser() === $this) {
                $tenPercentPlanWeek->setUser(null);
            }
        }

        return $this;
    }

    public function getTenPercentStartDate(): ?\DateTimeInterface
    {
        return $this->ten_percent_start_date;
    }

    public function setTenPercentStartDate(?\DateTimeInterface $ten_percent_start_date): self
    {
        $this->ten_percent_start_date = $ten_percent_start_date;

        return $this;
    }

    public function getTenPercentStartAmount(): ?float
    {
        return $this->ten_percent_start_amount;
    }

    public function setTenPercentStartAmount(?float $ten_percent_start_amount): self
    {
        $this->ten_percent_start_amount = $ten_percent_start_amount;

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
            $stock->setUser($this);
        }

        return $this;
    }

    public function removeStock(Stock $stock): self
    {
        if ($this->stocks->removeElement($stock)) {
            // set the owning side to null (unless already changed)
            if ($stock->getUser() === $this) {
                $stock->setUser(null);
            }
        }

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
            $dividend->setUser($this);
        }

        return $this;
    }

    public function removeDividend(Dividend $dividend): self
    {
        if ($this->dividends->removeElement($dividend)) {
            // set the owning side to null (unless already changed)
            if ($dividend->getUser() === $this) {
                $dividend->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, FuturesBuckets>
     */
    public function getFuturesBuckets(): Collection
    {
        return $this->futuresBuckets;
    }

    public function addFuturesBucket(FuturesBuckets $futuresBucket): self
    {
        if (!$this->futuresBuckets->contains($futuresBucket)) {
            $this->futuresBuckets[] = $futuresBucket;
            $futuresBucket->setUser($this);
        }

        return $this;
    }

    public function removeFuturesBucket(FuturesBuckets $futuresBucket): self
    {
        if ($this->futuresBuckets->removeElement($futuresBucket)) {
            // set the owning side to null (unless already changed)
            if ($futuresBucket->getUser() === $this) {
                $futuresBucket->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, FuturesDay>
     */
    public function getFuturesDays(): Collection
    {
        return $this->futuresDays;
    }

    public function addFuturesDay(FuturesDay $futuresDay): self
    {
        if (!$this->futuresDays->contains($futuresDay)) {
            $this->futuresDays[] = $futuresDay;
            $futuresDay->setUser($this);
        }

        return $this;
    }

    public function removeFuturesDay(FuturesDay $futuresDay): self
    {
        if ($this->futuresDays->removeElement($futuresDay)) {
            // set the owning side to null (unless already changed)
            if ($futuresDay->getUser() === $this) {
                $futuresDay->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, FuturesWeek>
     */
    public function getFuturesWeeks(): Collection
    {
        return $this->futuresWeeks;
    }

    public function addFuturesWeek(FuturesWeek $futuresWeek): self
    {
        if (!$this->futuresWeeks->contains($futuresWeek)) {
            $this->futuresWeeks[] = $futuresWeek;
            $futuresWeek->setUser($this);
        }

        return $this;
    }

    public function removeFuturesWeek(FuturesWeek $futuresWeek): self
    {
        if ($this->futuresWeeks->removeElement($futuresWeek)) {
            // set the owning side to null (unless already changed)
            if ($futuresWeek->getUser() === $this) {
                $futuresWeek->setUser(null);
            }
        }

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
            $option->setUser($this);
        }

        return $this;
    }

    public function removeOption(Option $option): self
    {
        if ($this->options->removeElement($option)) {
            // set the owning side to null (unless already changed)
            if ($option->getUser() === $this) {
                $option->setUser(null);
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
            $play->setUser($this);
        }

        return $this;
    }

    public function removePlay(Play $play): self
    {
        if ($this->plays->removeElement($play)) {
            // set the owning side to null (unless already changed)
            if ($play->getUser() === $this) {
                $play->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Notes>
     */
    public function getNotes(): Collection
    {
        return $this->notes;
    }

    public function addNote(Note $note): self
    {
        if (!$this->notes->contains($note)) {
            $this->notes[] = $note;
            $note->setUser($this);
        }

        return $this;
    }

    public function removeNote(Note $note): self
    {
        if ($this->notes->removeElement($note)) {
            // set the owning side to null (unless already changed)
            if ($note->getUser() === $this) {
                $note->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, WrittenOption>
     */
    public function getWrittenOptions(): Collection
    {
        return $this->writtenOptions;
    }

    public function addCoveredCall(WrittenOption $coveredCall): self
    {
        if (!$this->writtenOptions->contains($coveredCall)) {
            $this->writtenOptions[] = $coveredCall;
            $coveredCall->setUser($this);
        }

        return $this;
    }

    public function removeCoveredCall(WrittenOption $coveredCall): self
    {
        if ($this->writtenOptions->removeElement($coveredCall)) {
            // set the owning side to null (unless already changed)
            if ($coveredCall->getUser() === $this) {
                $coveredCall->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Portfolio>
     */
    public function getPortfolios(): Collection
    {
        return $this->portfolios;
    }

    public function addPortfolio(Portfolio $portfolio): self
    {
        if (!$this->portfolios->contains($portfolio)) {
            $this->portfolios[] = $portfolio;
            $portfolio->setUser($this);
        }

        return $this;
    }

    public function removePortfolio(Portfolio $portfolio): self
    {
        if ($this->portfolios->removeElement($portfolio)) {
            // set the owning side to null (unless already changed)
            if ($portfolio->getUser() === $this) {
                $portfolio->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TFSAContribution>
     */
    public function getTFSAContributions(): Collection
    {
        return $this->TFSAContributions;
    }

    public function addTFSAContribution(TFSAContribution $tFSAContribution): self
    {
        if (!$this->TFSAContributions->contains($tFSAContribution)) {
            $this->TFSAContributions[] = $tFSAContribution;
            $tFSAContribution->setUser($this);
        }

        return $this;
    }

    public function removeTFSAContribution(TFSAContribution $tFSAContribution): self
    {
        if ($this->TFSAContributions->removeElement($tFSAContribution)) {
            // set the owning side to null (unless already changed)
            if ($tFSAContribution->getUser() === $this) {
                $tFSAContribution->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, FHSAContribution>
     */
    public function getFHSAContributions(): Collection
    {
        return $this->FHSAContributions;
    }

    public function addFHSAContribution(FHSAContribution $FHSAContribution): self
    {
        if (!$this->FHSAContributions->contains($FHSAContribution)) {
            $this->FHSAContributions[] = $FHSAContribution;
            $FHSAContribution->setUser($this);
        }

        return $this;
    }

    public function removeFHSAContribution(FHSAContribution $FHSAContribution): self
    {
        if ($this->FHSAContributions->removeElement($FHSAContribution)) {
            // set the owning side to null (unless already changed)
            if ($FHSAContribution->getUser() === $this) {
                $FHSAContribution->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, RRSPContribution>
     */
    public function getRRSPContributions(): Collection
    {
        return $this->RRSPContributions;
    }

    public function addRRSPContribution(RRSPContribution $RRSPContribution): self
    {
        if (!$this->RRSPContributions->contains($RRSPContribution)) {
            $this->RRSPContributions[] = $RRSPContribution;
            $RRSPContribution->setUser($this);
        }

        return $this;
    }

    public function removeRRSPContribution(RRSPContribution $RRSPContribution): self
    {
        if ($this->RRSPContributions->removeElement($RRSPContribution)) {
            // set the owning side to null (unless already changed)
            if ($RRSPContribution->getUser() === $this) {
                $RRSPContribution->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TotalValue>
     */
    public function getTotalValues(): Collection
    {
        return $this->totalValues;
    }

    public function addTotalValue(TotalValue $totalValue): self
    {
        if (!$this->totalValues->contains($totalValue)) {
            $this->totalValues[] = $totalValue;
            $totalValue->setUser($this);
        }

        return $this;
    }

    public function removeTotalValue(TotalValue $totalValue): self
    {
        if ($this->totalValues->removeElement($totalValue)) {
            // set the owning side to null (unless already changed)
            if ($totalValue->getUser() === $this) {
                $totalValue->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Debt>
     */
    public function getDebts(): Collection
    {
        return $this->debts;
    }

    public function addDebt(Debt $debt): self
    {
        if (!$this->debts->contains($debt)) {
            $this->debts[] = $debt;
            $debt->setUser($this);
        }

        return $this;
    }

    public function removeDebt(Debt $debt): self
    {
        if ($this->debts->removeElement($debt)) {
            // set the owning side to null (unless already changed)
            if ($debt->getUser() === $this) {
                $debt->setUser(null);
            }
        }

        return $this;
    }
}
