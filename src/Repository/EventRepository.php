<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

//    public function findByCampusDateAndType($campus, $date)
//    {
//        return $this->createQueryBuilder('e')
//            ->join('e.host', 'o')
//            ->join('o.campus', 'c')
//            ->where('c = :campus')
//            ->andWhere('e.date = :date')
//            ->andWhere('e.isEvent = :isEvent')
//            ->setParameter('campus', $campus)
//            ->setParameter('date', $date)
//            ->setParameter('isEvent', true)
//            ->getQuery()
//            ->getResult();
//    }

//    /**
//     * @return Event[] Returns an array of Event objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('h')
//            ->andWhere('h.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('h.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Event
//    {
//        return $this->createQueryBuilder('h')
//            ->andWhere('h.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
