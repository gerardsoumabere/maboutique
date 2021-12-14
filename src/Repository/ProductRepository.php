<?php

namespace App\Repository;

use App\Entity\Product;
use App\SearchClass\Search;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    // /**
    //  * @return Product[] Returns an array of Product objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Product
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */


    /**
     * @param $slug
     * @return Product|null
     */
    public function findOneBySlug($slug): ?Product
    {
        try {
            return $this->createQueryBuilder('p')
                ->andWhere('p.slug = :val')
                ->setParameter('val', $slug)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    /**
     * @param Search $search
     * @return Product|array|null
     */
    public function findWithSearch(Search $search)
    {
        try {
            $query= $this
                ->createQueryBuilder('p')
                ->select('c','p')
                ->join('p.category','c');
            if (!empty($search->categories)){
                $query = $query
                    ->andWhere('c.id IN (:categories)')
                    ->setParameter('categories',$search->categories);
            }

            return $query->getQuery()->getResult();

        } catch (NonUniqueResultException $e) {
            return null;
        }
    }
}
