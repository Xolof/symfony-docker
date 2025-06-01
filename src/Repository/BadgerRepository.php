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
    public function getPaginated(?string $search): Pagerfanta
    {
        $entityManager = $this->getEntityManager();

        if ($search) {
            $search = "%$search%";
            $query = $entityManager->createQuery(
                'SELECT b
                FROM App\Entity\Badger b
                WHERE b.name LIKE :search
                OR b.continent LIKE :search
                OR b.description LIKE :search

                ORDER BY b.id DESC'
            )->setParameter('search', $search);

            return new Pagerfanta(new QueryAdapter($query));
        }

        $query = $entityManager->createQuery(
            'SELECT b
            FROM App\Entity\Badger b
            ORDER BY b.id DESC'
        );

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
