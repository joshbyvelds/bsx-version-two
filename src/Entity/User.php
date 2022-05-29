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

    public function __construct()
    {
        $this->transactions = new ArrayCollection();
        $this->tenPercentPlanWeeks = new ArrayCollection();
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
}
