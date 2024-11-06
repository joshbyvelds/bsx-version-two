<?php

namespace App\Entity;

use App\Repository\TotalValueRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TotalValueRepository::class)]
class TotalValue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'totalValue')]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[ORM\Column(type: 'integer')]
    private $type;

    #[ORM\Column(type: 'date')]
    private $date;

    #[ORM\Column(type: 'integer')]
    private $fill;

    #[ORM\Column(type: 'float')]
    private $value1;

    #[ORM\Column(type: 'float')]
    private $value2;

    #[ORM\Column(type: 'float')]
    private $value3;

    #[ORM\Column(type: 'float')]
    private $value4;

    #[ORM\Column(type: 'float')]
    private $value5;

    #[ORM\Column(type: 'float')]
    private $value6;

    #[ORM\Column(type: 'float')]
    private $value7;

    #[ORM\Column(type: 'float')]
    private $value8;

    #[ORM\Column(type: 'float')]
    private $value9;

    #[ORM\Column(type: 'float')]
    private $value10;

    #[ORM\Column(type: 'float')]
    private $value11;

    #[ORM\Column(type: 'float')]
    private $value12;

    #[ORM\Column(type: 'float')]
    private $value13;

    #[ORM\Column(type: 'float')]
    private $value14;

    #[ORM\Column(type: 'float')]
    private $value15;

    #[ORM\Column(type: 'float')]
    private $value16;

    #[ORM\Column(type: 'float')]
    private $value17;

    #[ORM\Column(type: 'float')]
    private $value18;

    #[ORM\Column(type: 'float')]
    private $value19;

    #[ORM\Column(type: 'float')]
    private $value20;

    #[ORM\Column(type: 'date')]
    private $value_date1;

    #[ORM\Column(type: 'date')]
    private $value_date2;

    #[ORM\Column(type: 'date')]
    private $value_date3;

    #[ORM\Column(type: 'date')]
    private $value_date4;

    #[ORM\Column(type: 'date')]
    private $value_date5;

    #[ORM\Column(type: 'date')]
    private $value_date6;

    #[ORM\Column(type: 'date')]
    private $value_date7;

    #[ORM\Column(type: 'date')]
    private $value_date8;

    #[ORM\Column(type: 'date')]
    private $value_date9;

    #[ORM\Column(type: 'date')]
    private $value_date10;

    #[ORM\Column(type: 'date')]
    private $value_date11;

    #[ORM\Column(type: 'date')]
    private $value_date12;

    #[ORM\Column(type: 'date')]
    private $value_date13;

    #[ORM\Column(type: 'date')]
    private $value_date14;

    #[ORM\Column(type: 'date')]
    private $value_date15;

    #[ORM\Column(type: 'date')]
    private $value_date16;

    #[ORM\Column(type: 'date')]
    private $value_date17;

    #[ORM\Column(type: 'date')]
    private $value_date18;

    #[ORM\Column(type: 'date')]
    private $value_date19;

    #[ORM\Column(type: 'date')]
    private $value_date20;

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

    public function getType(): ?float
    {
        return $this->type;
    }

    public function setType(float $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getFill(): ?float
    {
        return $this->fill;
    }

    public function setFill(float $value): self
    {
        $this->fill = $value;

        return $this;
    }

    public function increaseFill(): self
    {
        $this->fill += 1;
        return $this;
    }

    public function getValue1(): ?float
    {
        return $this->value1;
    }

    public function setValue1(float $value): self
    {
        $this->value1 = $value;

        return $this;
    }

    public function getValue2(): ?float
    {
        return $this->value2;
    }

    public function setValue2(float $value): self
    {
        $this->value2 = $value;

        return $this;
    }

    public function getValue3(): ?float
    {
        return $this->value3;
    }

    public function setValue3(float $value): self
    {
        $this->value3 = $value;

        return $this;
    }

    public function getValue4(): ?float
    {
        return $this->value4;
    }

    public function setValue4(float $value): self
    {
        $this->value4 = $value;

        return $this;
    }

    public function getValue5(): ?float
    {
        return $this->value5;
    }

    public function setValue5(float $value): self
    {
        $this->value5 = $value;

        return $this;
    }

    public function getValue6(): ?float
    {
        return $this->value6;
    }

    public function setValue6(float $value): self
    {
        $this->value6 = $value;

        return $this;
    }

    public function getValue7(): ?float
    {
        return $this->value7;
    }

    public function setValue7(float $value): self
    {
        $this->value7 = $value;

        return $this;
    }

    public function getValue8(): ?float
    {
        return $this->value8;
    }

    public function setValue8(float $value): self
    {
        $this->value8 = $value;

        return $this;
    }

    public function getValue9(): ?float
    {
        return $this->value9;
    }

    public function setValue9(float $value): self
    {
        $this->value9 = $value;

        return $this;
    }

    public function getValue10(): ?float
    {
        return $this->value10;
    }

    public function setValue10(float $value): self
    {
        $this->value10 = $value;

        return $this;
    }

    public function getValue11(): ?float
    {
        return $this->value11;
    }

    public function setValue11(float $value): self
    {
        $this->value11 = $value;

        return $this;
    }

    public function getValue12(): ?float
    {
        return $this->value12;
    }

    public function setValue12(float $value): self
    {
        $this->value12 = $value;

        return $this;
    }

    public function getValue13(): ?float
    {
        return $this->value13;
    }

    public function setValue13(float $value): self
    {
        $this->value13 = $value;

        return $this;
    }

    public function getValue14(): ?float
    {
        return $this->value14;
    }

    public function setValue14(float $value): self
    {
        $this->value14 = $value;

        return $this;
    }

    public function getValue15(): ?float
    {
        return $this->value15;
    }

    public function setValue15(float $value): self
    {
        $this->value15 = $value;

        return $this;
    }

    public function getValue16(): ?float
    {
        return $this->value16;
    }

    public function setValue16(float $value): self
    {
        $this->value16 = $value;

        return $this;
    }

    public function getValue17(): ?float
    {
        return $this->value17;
    }

    public function setValue17(float $value): self
    {
        $this->value17 = $value;

        return $this;
    }

    public function getValue18(): ?float
    {
        return $this->value18;
    }

    public function setValue18(float $value): self
    {
        $this->value18 = $value;

        return $this;
    }

    public function getValue19(): ?float
    {
        return $this->value19;
    }

    public function setValue19(float $value): self
    {
        $this->value19 = $value;

        return $this;
    }

    public function getValue20(): ?float
    {
        return $this->value20;
    }

    public function setValue20(float $value): self
    {
        $this->value20 = $value;

        return $this;
    }

    public function getValueDate1(): ?\DateTimeInterface
    {
        return $this->value_date1;
    }

    public function setValueDate1(\DateTimeInterface $date): self
    {
        $this->value_date1 = $date;

        return $this;
    }

    public function getValueDate2(): ?\DateTimeInterface
    {
        return $this->value_date2;
    }

    public function setValueDate2(\DateTimeInterface $date): self
    {
        $this->value_date2 = $date;

        return $this;
    }

    public function getValueDate3(): ?\DateTimeInterface
    {
        return $this->value_date3;
    }

    public function setValueDate3(\DateTimeInterface $date): self
    {
        $this->value_date3 = $date;

        return $this;
    }

    public function getValueDate4(): ?\DateTimeInterface
    {
        return $this->value_date4;
    }

    public function setValueDate4(\DateTimeInterface $date): self
    {
        $this->value_date4 = $date;

        return $this;
    }

    public function getValueDate5(): ?\DateTimeInterface
    {
        return $this->value_date5;
    }

    public function setValueDate5(\DateTimeInterface $date): self
    {
        $this->value_date5 = $date;

        return $this;
    }

    public function getValueDate6(): ?\DateTimeInterface
    {
        return $this->value_date6;
    }

    public function setValueDate6(\DateTimeInterface $date): self
    {
        $this->value_date6 = $date;

        return $this;
    }

    public function getValueDate7(): ?\DateTimeInterface
    {
        return $this->value_date7;
    }

    public function setValueDate7(\DateTimeInterface $date): self
    {
        $this->value_date7 = $date;

        return $this;
    }

    public function getValueDate8(): ?\DateTimeInterface
    {
        return $this->value_date8;
    }

    public function setValueDate8(\DateTimeInterface $date): self
    {
        $this->value_date8 = $date;

        return $this;
    }

    public function getValueDate9(): ?\DateTimeInterface
    {
        return $this->value_date9;
    }

    public function setValueDate9(\DateTimeInterface $date): self
    {
        $this->value_date9 = $date;

        return $this;
    }

    public function getValueDate10(): ?\DateTimeInterface
    {
        return $this->value_date10;
    }

    public function setValueDate10(\DateTimeInterface $date): self
    {
        $this->value_date10= $date;

        return $this;
    }

    public function getValueDate11(): ?\DateTimeInterface
    {
        return $this->value_date11;
    }

    public function setValueDate11(\DateTimeInterface $date): self
    {
        $this->value_date11 = $date;

        return $this;
    }

    public function getValueDate12(): ?\DateTimeInterface
    {
        return $this->value_date12;
    }

    public function setValueDate12(\DateTimeInterface $date): self
    {
        $this->value_date12 = $date;

        return $this;
    }

    public function getValueDate13(): ?\DateTimeInterface
    {
        return $this->value_date13;
    }

    public function setValueDate13(\DateTimeInterface $date): self
    {
        $this->value_date13 = $date;

        return $this;
    }

    public function getValueDate14(): ?\DateTimeInterface
    {
        return $this->value_date14;
    }

    public function setValueDate14(\DateTimeInterface $date): self
    {
        $this->value_date14 = $date;

        return $this;
    }

    public function getValueDate15(): ?\DateTimeInterface
    {
        return $this->value_date15;
    }

    public function setValueDate15(\DateTimeInterface $date): self
    {
        $this->value_date15 = $date;

        return $this;
    }

    public function getValueDate16(): ?\DateTimeInterface
    {
        return $this->value_date16;
    }

    public function setValueDate16(\DateTimeInterface $date): self
    {
        $this->value_date16 = $date;

        return $this;
    }

    public function getValueDate17(): ?\DateTimeInterface
    {
        return $this->value_date17;
    }

    public function setValueDate17(\DateTimeInterface $date): self
    {
        $this->value_date17 = $date;

        return $this;
    }

    public function getValueDate18(): ?\DateTimeInterface
    {
        return $this->value_date18;
    }

    public function setValueDate18(\DateTimeInterface $date): self
    {
        $this->value_date18 = $date;

        return $this;
    }

    public function getValueDate19(): ?\DateTimeInterface
    {
        return $this->value_date19;
    }

    public function setValueDate19(\DateTimeInterface $date): self
    {
        $this->value_date19 = $date;

        return $this;
    }

    public function getValueDate20(): ?\DateTimeInterface
    {
        return $this->value_date20;
    }

    public function setValueDate20(\DateTimeInterface $date): self
    {
        $this->value_date20 = $date;

        return $this;
    }

    public function moveLeft(): void
    {
        $this->value1 = $this->value2;
        $this->value2 = $this->value3;
        $this->value3 = $this->value4;
        $this->value4 = $this->value5;
        $this->value5 = $this->value6;
        $this->value6 = $this->value7;
        $this->value7 = $this->value8;
        $this->value8 = $this->value9;
        $this->value9 = $this->value10;
        $this->value10 = $this->value11;
        $this->value11 = $this->value12;
        $this->value12 = $this->value13;
        $this->value13 = $this->value14;
        $this->value14 = $this->value15;
        $this->value15 = $this->value16;
        $this->value16 = $this->value17;
        $this->value17 = $this->value18;
        $this->value18 = $this->value19;
        $this->value19 = $this->value20;

        $this->value_date1 = $this->value_date2;
        $this->value_date2 = $this->value_date3;
        $this->value_date3 = $this->value_date4;
        $this->value_date4 = $this->value_date5;
        $this->value_date5 = $this->value_date6;
        $this->value_date6 = $this->value_date7;
        $this->value_date7 = $this->value_date8;
        $this->value_date8 = $this->value_date9;
        $this->value_date9 = $this->value_date10;
        $this->value_date10 = $this->value_date11;
        $this->value_date11 = $this->value_date12;
        $this->value_date12 = $this->value_date13;
        $this->value_date13 = $this->value_date14;
        $this->value_date14 = $this->value_date15;
        $this->value_date15 = $this->value_date16;
        $this->value_date16 = $this->value_date17;
        $this->value_date17 = $this->value_date18;
        $this->value_date18 = $this->value_date19;
        $this->value_date19 = $this->value_date20;
    }
}
