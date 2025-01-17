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

    /**
     * Récupère toutes les transactions liées aux comptes bancaires d'un utilisateur.
     *
     * @param int $userId
     * @return Transaction[]
     */
    public function findTransactionsByUserId(int $userId): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.source_account', 'source') // Relation avec le compte source
            ->leftJoin('t.destination_account', 'dest') // Relation avec le compte destination
            ->leftJoin('source.owner', 'ownerSource') // Propriétaire du compte source
            ->leftJoin('dest.owner', 'ownerDest') // Propriétaire du compte destination
            ->andWhere('ownerSource.id = :userId OR ownerDest.id = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('t.date_time', 'DESC') // Tri par date décroissante
            ->getQuery()
            ->getResult();
    }



    // /**
    //  * Récupère toutes les transactions liées à un utilisateur donné.
    //  *
    //  * @param int $userId
    //  * @return Transaction[]
    //  */
    // public function findTransactionsByUserId(int $userId): array
    // {
    //     return $this->createQueryBuilder('t')
    //         ->innerJoin('t.user', 'u') // Relation avec l'utilisateur
    //         ->andWhere('u.id = :userId')
    //         ->setParameter('userId', $userId)
    //         ->orderBy('t.date', 'DESC') // Tri par date décroissante
    //         ->getQuery()
    //         ->getResult();
    // }

}
