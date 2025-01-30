<?php

namespace App\Entity;

use App\Repository\OptionRolloverRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OptionRolloverRepository::class)]
class OptionRollover
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Option::class, inversedBy: 'optionRollovers')]
    #[ORM\JoinColumn(nullable: false)]
    private $option_id;

    #[ORM\Column(type: 'integer')]
    private $contracts;

    #[ORM\Column(type: 'float')]
    private $old_price;

    #[ORM\Column(type: 'float')]
    private $new_price;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOptionId(): ?Option
    {
        return $this->option_id;
    }

    public function setOptionId(?Option $option_id): self
    {
        $this->option_id = $option_id;

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

    public function getOldPrice(): ?float
    {
        return $this->old_price;
    }

    public function setOldPrice(float $old_price): self
    {
        $this->old_price = $old_price;

        return $this;
    }

    public function getNewPrice(): ?float
    {
        return $this->new_price;
    }

    public function setNewPrice(float $new_price): self
    {
        $this->new_price = $new_price;

        return $this;
    }
}
