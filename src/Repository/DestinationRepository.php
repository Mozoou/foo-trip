<?php

namespace App\Repository;

use App\Entity\Destination;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Destination>
 */
class DestinationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Destination::class);
    }

    /**
     * @return Destination[]
     */
    public function findByFilters(?string $name = null): array
    {
        $qb = $this->createQueryBuilder('d');

        if ($name !== null && $name !== '') {
            $qb->andWhere('d.name LIKE :name')
               ->setParameter('name', '%' . $name . '%');
        }

        return $qb->orderBy('d.id', 'ASC')->getQuery()->getResult();
    }
}
