<?php

namespace App\Entity;

use App\Enum\TransactionStatus;
use App\Enum\TransactionType;
use App\Repository\TransactionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransactionRepository::class)]
class Transaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', enumType: TransactionType::class)]
    private TransactionType $type;

    #[ORM\Column]
    private ?int $amount = null;

    #[ORM\Column(type: 'string', enumType: TransactionStatus::class)]
    private TransactionStatus $status;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_time = null;

    #[ORM\ManyToOne(inversedBy: 'transactions_issued')]
    #[ORM\JoinColumn]
    private ?BankAccount $source_account = null;

    #[ORM\ManyToOne(inversedBy: 'transactions_received')]
    #[ORM\JoinColumn]
    private ?BankAccount $destination_account = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?TransactionType
    {
        return $this->type;
    }

    public function setType( TransactionType  $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getStatus(): ?TransactionStatus
    {
        return $this->status;
    }

    public function setStatus(TransactionStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getDateTime(): ?\DateTimeInterface
    {
        return $this->date_time;
    }

    public function setDateTime(\DateTimeInterface $date_time): static
    {
        $this->date_time = $date_time;

        return $this;
    }

    public function getSourceAccount(): ?BankAccount
    {
        return $this->source_account;
    }

    public function setSourceAccount(?BankAccount $source_account): static
    {
        $this->source_account = $source_account;

        return $this;
    }

    public function getDestinationAccount(): ?BankAccount
    {
        return $this->destination_account;
    }

    public function setDestinationAccount(?BankAccount $destination_account): static
    {
        $this->destination_account = $destination_account;

        return $this;
    }
}
