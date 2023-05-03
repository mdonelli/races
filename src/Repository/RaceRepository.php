<?php

namespace App\Repository;



use App\Entity\Race;
use App\Entity\RaceResult;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

class RaceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Race::class);
    }

    public function getRaceCollection(array $filters = [])
    {
        $builder = $this->getEntityManager()->createQueryBuilder()
            ->select('r.title as title, r.date as date,
            from_unixtime(avg(unix_timestamp(cast(rrm.time as DateTime))), :timeFormat) as avg_time_medium,
            from_unixtime(avg(unix_timestamp(cast(rrl.time as DateTime))), :timeFormat) as avg_time_long')
            ->from(Race::class, 'r')
            ->join(RaceResult::class, 'rrm', Join::WITH, 'r = rrm.race and rrm.distance = :medium')
            ->join(RaceResult::class, 'rrl', Join::WITH, 'r = rrl.race and rrl.distance = :long')
        ->where('1=1')
        ->setParameter('timeFormat', '%H:%i:%s')
        ->setParameter('medium', RaceResult::DISTANCE_MEDIUM)
        ->setParameter('long', RaceResult::DISTANCE_LONG)
        ->groupBy('title, date');

        if (!empty($filters['title'])) {
            $builder->andWhere('r.title like :title')
                ->setParameter('title', "%{$filters['title']}%");
        }

        $builder->orderBy($filters['order'] ?? 'title', $filters['direction'] ?? 'asc');

        return $builder->getQuery()
            ->getScalarResult();
    }

    public function getRaces() :array
    {
        return $this->createQueryBuilder('r')
            ->select('r.id', 'r.title')
            ->getQuery()
            ->getScalarResult();
    }
}