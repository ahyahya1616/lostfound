<?php

namespace App\Repository;

use App\Entity\LostObject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LostObject>
 */
class LostObjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LostObject::class);
    }

    public function searchObjects(?string $query, ?string $category, ?string $location)
    {
        $qb = $this->createQueryBuilder('o')
            ->leftJoin('o.category', 'c');
    
        if ($query) {
            $qb->andWhere('o.title LIKE :query OR o.description LIKE :query')
               ->setParameter('query', '%' . $query . '%');
        }
    
        if ($category) {
            $qb->andWhere('c.id = :category')
               ->setParameter('category', $category);
        }
    
        if ($location) {
            $qb->andWhere('o.location LIKE :location')
               ->setParameter('location', '%' . $location . '%');
        }
    
        return $qb->getQuery()->getResult();
    }



    //    /**
    //     * @return LostObject[] Returns an array of LostObject objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('l.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?LostObject
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
