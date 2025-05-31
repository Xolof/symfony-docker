<?php

namespace App\Repository;

use App\Entity\Badger;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Doctrine\ORM\QueryAdapter;

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

    /**
     * Get paginated badgers.
     *
     * @return Pagerfanta<Badger> A paginator of Badgers.
     */
    public function getPaginated(): Pagerfanta
    {
        $query = $this->createQueryBuilder('b')
            ->orderBy('b.id', 'DESC')
            ->getQuery();

        return new Pagerfanta(new QueryAdapter($query));
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
