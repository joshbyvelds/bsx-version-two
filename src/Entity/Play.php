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
}
