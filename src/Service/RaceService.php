<?php

namespace App\Service;

use App\Entity\Race;
use App\Entity\RaceResult;
use App\Repository\RaceRepository;
use App\Repository\RaceResultRepository;
use Doctrine\ORM\EntityManagerInterface;

class RaceService
{
    const MAX_RESULTS = 100;

    private EntityManagerInterface $entityManager;
    private RaceRepository $raceRepository;
    private RaceResultRepository $raceResultRepository;
    public function __construct(EntityManagerInterface $entityManager, RaceRepository $raceRepository, RaceResultRepository $raceResultRepository)
    {
        $this->entityManager = $entityManager;
        $this->raceRepository = $raceRepository;
        $this->raceResultRepository = $raceResultRepository;
    }

    /**
     * @param string $title
     * @param string $date
     * @param string $filePath
     * @return bool
     */
    public function importFromFile(string $title, string $date, string $filePath) :bool
    {
        try {
            $this->entityManager->getConnection()->beginTransaction();

            $this->handleImport($title, $date, $filePath) ?
                $this->entityManager->getConnection()->commit() : $this->entityManager->getConnection()->rollBack();

            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }

    public function getRaceCollection(array $filters = []) :array
    {
        return $this->raceRepository->getRaceCollection($filters);
    }

    public function getResultsByRace(Race $race, array $filters = []) :array
    {
        return $this->raceResultRepository->getResultsByRace($race, $filters);
    }

    public function getResults(Race $race) :array
    {
        return $this->raceResultRepository->getResults($race);
    }

    /**
     * @throws \Exception
     */
    public function editResult(RaceResult $raceResult, array $values) :void
    {
        $raceResult->setFullName($values['full_name']);
        $raceResult->setTime(new \DateTime($values['time']));
        $raceResult->setDistance($values['distance']);
        $raceResult->setAgeCategory($values['age_category']);

        $this->entityManager->flush();
    }

    public function getRaces() :array
    {
        return $this->raceRepository->getRaces();
    }

    private function handleImport(string $title, string $date, string $filePath) :bool
    {
        try {
            $race = new Race();
            $race->setTitle($title);
            $race->setDate(new \DateTime($date));
            $this->entityManager->persist($race);
            $this->entityManager->flush();

            $raceRef = $this->entityManager->getReference(Race::class, $race->getId());

            $handle = fopen($filePath, 'r');
            if (!$handle) {
                return false;
            }
            fgets($handle); //discard title line
            $counter = 0;

            while ($line = fgets($handle)) {
                $values = explode(',', $line);

                $raceResult = new RaceResult();
                $raceResult->setFullName($values[0]);
                $raceResult->setDistance($values[1]);
                $raceResult->setTime(new \DateTime($values[2]));
                $raceResult->setAgeCategory($values[3]);
                $raceResult->setRace($raceRef);
                $this->entityManager->persist($raceResult);
                $counter++;

                //flush every MAX_RESULTS
                if ($counter == self::MAX_RESULTS) {
                    $this->entityManager->flush();
                    $this->entityManager->clear();
                }
            }

            $this->entityManager->flush();

            fclose($handle);

            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }
}