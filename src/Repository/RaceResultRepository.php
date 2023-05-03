<?php

namespace App\Repository;

use App\Entity\Race;
use App\Entity\RaceResult;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RaceResultRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RaceResult::class);
    }

    public function getResultsByRace(Race $race, array $filters = []): array
    {
        $builder = $this->getEntityManager()->createQueryBuilder()
            ->select('rr.fullName as full_name, rr.distance as distance, rr.time as time, rr.ageCategory as age_category')
            ->addSelect('( ' . $this->getEntityManager()
                    ->createQueryBuilder()
                    ->select('case when rr.distance = :medium then :val else 1 + count(1) end')
                    ->from(RaceResult::class, 'overall')
                    ->where('overall.time < rr.time')
                    ->andWhere('overall.race = rr.race')
                    ->andWhere('overall.distance <> :medium')
                    ->getDQL() . ') as overall_placement')
            ->addSelect('( ' . $this->getEntityManager()
                    ->createQueryBuilder()
                    ->select('case when rr.distance = :medium then :val else 1 + count(1) end')
                    ->from(RaceResult::class, 'categorical')
                    ->where('categorical.time < rr.time')
                    ->andWhere('categorical.race = rr.race')
                    ->andWhere('categorical.distance <> :medium')
                    ->andWhere('categorical.ageCategory = rr.ageCategory')
                    ->getDQL() . ') as category_placement')
            ->from(RaceResult::class, 'rr')
            ->where('rr.race = :race')
            ->setParameter('race', $race)
            ->setParameter('medium', RaceResult::DISTANCE_MEDIUM)
            ->setParameter('val', null);

        if (!empty($filters['full_name'])) {
            $builder->andWhere('rr.fullName like :full_name')
                ->setParameter('full_name', "%{$filters['full_name']}%");
        }

        if (!empty($filters['distance'])) {
            $builder->andWhere('rr.distance = :distance')
                ->setParameter('distance', $filters['distance']);
        }

        if (!empty($filters['age_category'])) {
            $builder->andWhere('rr.ageCategory like :age_category')
                ->setParameter('age_category', "%{$filters['age_category']}%");
        }

        $builder->orderBy($filters['order'] ?? 'time', $filters['direction'] ?? 'asc');

        return $builder->getQuery()
            ->getScalarResult();
    }

    public function getResults(Race $race): array
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('rr.id as id, rr.fullName as full_name, rr.distance as distance, rr.time as time, rr.ageCategory as age_category')
            ->from(RaceResult::class, 'rr')
            ->where('rr.race = :race')
            ->setParameter('race', $race)
            ->getQuery()
            ->getScalarResult();
    }
}