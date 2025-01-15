<?php

namespace App\Entity;

use App\Enum\BankAccountType;
use App\Repository\BankAccountRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\MaxDepth;

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

    public function getType(): BankAccountType {
        return $this->type;
    }

    public function setType(BankAccountType $type): void {
        $this->type = $type;
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

    /**
     * @return Collection<int, Transaction>
     */
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

    public function removeTransactionsIssued(Transaction $transactionsIssued): static
    {
        if ($this->transactions_issued->removeElement($transactionsIssued)) {
            // set the owning side to null (unless already changed)
            if ($transactionsIssued->getSourceAccount() === $this) {
                $transactionsIssued->setSourceAccount(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Transaction>
     */
    public function getTransactionsReceived(): Collection
    {
        return $this->transactions_received;
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
            // set the owning side to null (unless already changed)
            if ($transactionsReceived->getDestinationAccount() === $this) {
                $transactionsReceived->setDestinationAccount(null);
            }
        }

        return $this;
    }
}
