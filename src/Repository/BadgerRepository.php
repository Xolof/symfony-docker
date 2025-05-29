<?php

namespace App\Repository;

use App\Entity\Badger;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository for Badger.
 * Can be used to make custom ORM functions.
 *
 * @extends ServiceEntityRepository<Badger>
 */
class BadgerRepository extends ServiceEntityRepository
{
    /**
     * Constructor
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Badger::class);
    }

    // **
    // * @return Badger[] Returns an array of Badger objects
    // */
    // public function findByExampleField($value): array
    // {
    // return $this->createQueryBuilder('b')
    // ->andWhere('b.exampleField = :val')
    // ->setParameter('val', $value)
    // ->orderBy('b.id', 'ASC')
    // ->setMaxResults(10)
    // ->getQuery()
    // ->getResult()
    // ;
    // }
    // public function findOneBySomeField($value): ?Badger
    // {
    // return $this->createQueryBuilder('b')
    // ->andWhere('b.exampleField = :val')
    // ->setParameter('val', $value)
    // ->getQuery()
    // ->getOneOrNullResult()
    // ;
    // }
}
