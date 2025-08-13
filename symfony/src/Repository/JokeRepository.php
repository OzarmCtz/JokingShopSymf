<?php

namespace App\Repository;

use App\Entity\Joke;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Joke>
 */
class JokeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Joke::class);
    }

    /**
     * Count jokes created after a specific date
     */
    public function countRecentJokes(\DateTimeImmutable $since): int
    {
        return $this->createQueryBuilder('j')
            ->select('COUNT(j.id)')
            ->andWhere('j.created_at >= :since')
            ->setParameter('since', $since)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Retrieve active jokes with optional filtering.
     *
     * @param int|null $categoryId
     * @param float|null $minPrice
     * @param float|null $maxPrice
     * @return Joke[]
     */
    public function findActiveJokes(?int $categoryId = null, ?float $minPrice = null, ?float $maxPrice = null): array
    {
        $qb = $this->createQueryBuilder('j')
            ->andWhere('j.is_active = :active')
            ->setParameter('active', true)
            ->leftJoin('j.category', 'c')
            ->addSelect('c');

        if ($categoryId) {
            $qb->andWhere('c.id = :cat')->setParameter('cat', $categoryId);
        }
        if ($minPrice !== null) {
            $qb->andWhere('j.price >= :min')->setParameter('min', $minPrice);
        }
        if ($maxPrice !== null) {
            $qb->andWhere('j.price <= :max')->setParameter('max', $maxPrice);
        }

        return $qb->getQuery()->getResult();
    }

    //    /**
    //     * @return Joke[] Returns an array of Joke objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('j')
    //            ->andWhere('j.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('j.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Joke
    //    {
    //        return $this->createQueryBuilder('j')
    //            ->andWhere('j.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
