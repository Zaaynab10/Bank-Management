<?php

namespace App\Entity;

use App\Enum\BankAccountStatus;
use App\Enum\BankAccountType;
use App\Repository\BankAccountRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BankAccountRepository::class)]
class BankAccount
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'bankAccounts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\Column(type: 'string', enumType: BankAccountType::class)]
    private BankAccountType $type;

    #[ORM\Column(type: 'string', enumType: BankAccountStatus::class)]
    private BankAccountStatus $status;

    #[ORM\Column(type: 'string')]
    private string $account_number;

    #[ORM\Column(type: 'float')]
    private float $balance = 100.00;

    /**
     * @var Collection<int, Transaction>
     */
    #[ORM\OneToMany(targetEntity: Transaction::class, mappedBy: 'compte_source')]
    private Collection $transactions_issued;

    /**
     * @var Collection<int, Transaction>
     */
    #[ORM\OneToMany(targetEntity: Transaction::class, mappedBy: 'destination_account')]
    private Collection $transactions_received;

    public function __construct()
    {
        $this->transactions_issued = new ArrayCollection();
        $this->transactions_received = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function getType(): BankAccountType
    {
        return $this->type;
    }

    public function setType(BankAccountType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getStatus(): BankAccountStatus
    {
        return $this->status;
    }

    public function setStatus(BankAccountStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getAccountNumber(): string
    {
        return $this->account_number;
    }

    public function setAccountNumber(string $account_number): static
    {
        $this->account_number = $account_number;

        return $this;
    }

    public function getBalance(): float
    {
        return $this->balance;
    }

    public function setBalance(float $balance): static
    {
        $this->balance = $balance;

        return $this;
    }

    public function getTransactionsIssued(): Collection
    {
        return $this->transactions_issued;
    }

    public function addTransactionsIssued(Transaction $transactionsIssued): static
    {
        if (!$this->transactions_issued->contains($transactionsIssued)) {
            $this->transactions_issued->add($transactionsIssued);
            $transactionsIssued->setSourceAccount($this);
        }

        return $this;
    }

    public function canWithdraw(float $amount): bool
{
    if ($this->type === BankAccountType::CURRENT) {
        return ($this->balance - $amount >= -400);
    }

    return ($this->balance - $amount >= 0);
}

public function canDeposit(float $amount): bool
{
    if ($this->type === BankAccountType::SAVINGS) {
        return ($this->balance + $amount <= 25000);
    }

    return true; }
    

    public function removeTransactionsIssued(Transaction $transactionsIssued): static
    {
        if ($this->transactions_issued->removeElement($transactionsIssued)) {
            if ($transactionsIssued->getSourceAccount() === $this) {
                $transactionsIssued->setSourceAccount(null);
            }
        }

        return $this;
    }

    public function getTransactionsReceived(): Collection
    {
        return $this->transactions_received;
    }


    public function isActive(): bool
{
    return $this->status === BankAccountStatus::ACTIVE;
}


    public function addTransactionsReceived(Transaction $transactionsReceived): static
    {
        if (!$this->transactions_received->contains($transactionsReceived)) {
            $this->transactions_received->add($transactionsReceived);
            $transactionsReceived->setDestinationAccount($this);
        }

        return $this;
    }

    public function removeTransactionsReceived(Transaction $transactionsReceived): static
    {
        if ($this->transactions_received->removeElement($transactionsReceived)) {
            if ($transactionsReceived->getDestinationAccount() === $this) {
                $transactionsReceived->setDestinationAccount(null);
            }
        }

        return $this;
    }
}
