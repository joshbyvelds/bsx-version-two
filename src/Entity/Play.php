<?php

namespace App\Entity;

use App\Repository\PlayRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlayRepository::class)]
class Play
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Stock::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $stock;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'plays')]
    #[ORM\JoinColumn(nullable: false)]
    private $User;

    #[ORM\ManyToMany(targetEntity: ShareBuy::class)]
    private $shares;

    #[ORM\ManyToMany(targetEntity: Option::class)]
    private $options;

    #[ORM\Column(type: 'boolean')]
    private $finished;

    #[ORM\Column(type: 'float')]
    private $share_average;

    #[ORM\Column(type: 'integer')]
    private $shares_remaining;

    #[ORM\Column(type: 'integer')]
    private $shares_total;

    #[ORM\Column(type: 'float')]
    private $contracts_average;

    #[ORM\Column(type: 'integer')]
    private $contracts_remaining;

    #[ORM\Column(type: 'integer')]
    private $contracts_total;

    #[ORM\Column(type: 'float')]
    private $shares_earned;

    #[ORM\Column(type: 'float')]
    private $options_earned;

    #[ORM\Column(type: 'date')]
    private $start_date;

    #[ORM\Column(type: 'date', nullable: true)]
    private $end_date;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\Column(type: 'integer')]
    private $shares_total_buys;

    #[ORM\Column(type: 'integer')]
    private $shares_total_sells;

    #[ORM\Column(type: 'integer')]
    private $contracts_total_buys;

    #[ORM\Column(type: 'integer')]
    private $contracts_total_sells;

    #[ORM\Column(type: 'float')]
    private $total_sold;

    #[ORM\Column(type: 'float')]
    private $written_option_total;

    public function __construct()
    {
        $this->shares = new ArrayCollection();
        $this->options = new ArrayCollection();
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

    public function getUser(): ?User
    {
        return $this->User;
    }

    public function setUser(?User $User): self
    {
        $this->User = $User;

        return $this;
    }

    /**
     * @return Collection<int, ShareBuy>
     */
    public function getShares(): Collection
    {
        return $this->shares;
    }

    public function addShare(ShareBuy $share): self
    {
        if (!$this->shares->contains($share)) {
            $this->shares[] = $share;
        }

        return $this;
    }

    public function removeShare(ShareBuy $share): self
    {
        $this->shares->removeElement($share);

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
        }

        return $this;
    }

    public function removeOption(Option $option): self
    {
        $this->options->removeElement($option);

        return $this;
    }

    public function isFinished(): ?bool
    {
        return $this->finished;
    }

    public function setFinished(bool $finished): self
    {
        $this->finished = $finished;

        return $this;
    }

    public function getSharesRemaining(): ?int
    {
        return $this->shares_remaining;
    }

    public function setSharesRemaining(int $shares_remaining): self
    {
        $this->shares_remaining = $shares_remaining;

        return $this;
    }

    public function getSharesTotal(): ?int
    {
        return $this->shares_total;
    }

    public function setSharesTotal(int $shares_total): self
    {
        $this->shares_total = $shares_total;

        return $this;
    }

    public function getShareAverage(): ?float
    {
        return $this->share_average;
    }

    public function setShareAverage(float $contracts_average): self
    {
        $this->contracts_average = $contracts_average;

        return $this;
    }

    public function getContractsAverage(): ?float
    {
        return $this->share_average;
    }

    public function setContractsAverage(float $contracts_average): self
    {
        $this->contracts_average = $contracts_average;

        return $this;
    }

    public function getContractsRemaining(): ?int
    {
        return $this->contracts_remaining;
    }

    public function setContractsRemaining(int $contracts_remaining): self
    {
        $this->contracts_remaining = $contracts_remaining;

        return $this;
    }

    public function getContractsTotal(): ?int
    {
        return $this->contracts_total;
    }

    public function setContractsTotal(int $contracts_total): self
    {
        $this->contracts_total = $contracts_total;

        return $this;
    }

    public function getSharesEarned(): ?float
    {
        return $this->shares_earned;
    }

    public function setSharesEarned(float $shares_earned): self
    {
        $this->shares_earned = $shares_earned;

        return $this;
    }

    public function getOptionsEarned(): ?float
    {
        return $this->options_earned;
    }

    public function setOptionsEarned(float $options_earned): self
    {
        $this->options_earned = $options_earned;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->start_date;
    }

    public function setStartDate(\DateTimeInterface $start_date): self
    {
        $this->start_date = $start_date;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->end_date;
    }

    public function setEndDate(?\DateTimeInterface $end_date): self
    {
        $this->end_date = $end_date;

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

    public function getSharesTotalBuys(): ?int
    {
        return $this->shares_total_buys;
    }

    public function setSharesTotalBuys(int $shares_total_buys): self
    {
        $this->shares_total_buys = $shares_total_buys;

        return $this;
    }

    public function getSharesTotalSells(): ?int
    {
        return $this->shares_total_sells;
    }

    public function setSharesTotalSells(int $shares_total_sells): self
    {
        $this->shares_total_sells = $shares_total_sells;

        return $this;
    }

    public function getContractsTotalBuys(): ?int
    {
        return $this->contracts_total_buys;
    }

    public function setContractsTotalBuys(int $contracts_total_buys): self
    {
        $this->contracts_total_buys = $contracts_total_buys;

        return $this;
    }

    public function getContractsTotalSells(): ?int
    {
        return $this->contracts_total_sells;
    }

    public function setContractsTotalSells(int $contracts_total_sells): self
    {
        $this->contracts_total_sells = $contracts_total_sells;

        return $this;
    }

    public function getTotalSold(): ?float
    {
        return $this->total_sold;
    }

    public function setTotalSold(float $total_sold): self
    {
        $this->total_sold = $total_sold;

        return $this;
    }

    public function getWrittenOptionTotal(): ?float
    {
        return $this->written_option_total;
    }

    public function setWrittenOptionTotal(float $written_option_total): self
    {
        $this->written_option_total = $written_option_total;

        return $this;
    }
}
