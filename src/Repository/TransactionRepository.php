<?php

namespace App\Repository;

use App\Entity\Transaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Transaction>
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    public function getBalanceByAccount(int $accountId): float
    {
        $qb = $this->createQueryBuilder('t');

        $qb->select('
            SUM(CASE 
                WHEN t.destination_account = :compteId THEN t.amount
                ELSE 0
            END) - 
            SUM(CASE 
                WHEN t.source_account = :compteId THEN t.amount
                ELSE 0
            END) as solde
        ')
            ->setParameter('compteId', $accountId);

        return $qb->getQuery()->getSingleScalarResult();
    }

}