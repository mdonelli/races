<?php

namespace App\Tests\Service;

use App\Entity\Race;
use App\Entity\RaceResult;
use App\Service\RaceService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use function PHPUnit\Framework\assertTrue;

class RaceServiceTest extends KernelTestCase
{
    private RaceService $raceService;
    private EntityManagerInterface $entityManager;

    public function setUp(): void
    {
        self::bootKernel();
        $this->raceService = static::getContainer()->get(RaceService::class);
        $this->entityManager =  static::getContainer()->get(EntityManagerInterface::class);
    }

    public function testImportFromFile()
    {
        self::assertTrue($this->handleImport('first', '1/1/2021'));

        /** @var Race $race */
        $race = $this->entityManager->getRepository(Race::class)->findOneBy(['title' => 'first']);
        self::assertNotEmpty($race);

        /** @var RaceResult[] $results */
        $results = $this->entityManager->getRepository(RaceResult::class)->findAll();
        self::assertCount(10, $results);
        self::assertTrue($race === $results[0]->getRace());
    }

    public function testGetRaceCollection()
    {
        $this->handleImport('first', '1/1/2021');
        $this->handleImport('second', '2/2/2022');
        $result = $this->raceService->getRaceCollection();
        self::assertCount(2, $result);
        assertTrue(isset($result[0]['title']));
        assertTrue(isset($result[0]['date']));
        assertTrue(isset($result[0]['avg_time_long']));
        assertTrue(isset($result[0]['avg_time_medium']));
    }

    public function testGetResultsByRace()
    {
        $this->handleImport('first', '1/1/2021');
        $this->handleImport('second', '2/2/2022');

        /** @var Race $race */
        $race = $this->entityManager->getRepository(Race::class)->findOneBy(['title' => 'first']);
        self::assertNotEmpty($race);

        $results = $this->raceService->getResultsByRace($race);
        self::assertCount(10, $results);
        assertTrue(isset($results[0]['full_name']));
        assertTrue(isset($results[0]['time']));
        assertTrue(isset($results[0]['distance']));
        assertTrue(isset($results[0]['age_category']));
        assertTrue(array_key_exists('overall_placement', $results[0]));
        assertTrue(array_key_exists('category_placement', $results[0]));
    }

    public function testGetResults()
    {
        $this->handleImport('first', '1/1/2021');
        $this->handleImport('second', '2/2/2022');

        /** @var Race $race */
        $race = $this->entityManager->getRepository(Race::class)->findOneBy(['title' => 'first']);
        self::assertNotEmpty($race);

        $results = $this->raceService->getResults($race);
        self::assertCount(10, $results);
        assertTrue(isset($results[0]['id']));
        assertTrue(isset($results[0]['full_name']));
        assertTrue(isset($results[0]['time']));
        assertTrue(isset($results[0]['distance']));
        assertTrue(isset($results[0]['age_category']));
    }

    public function testEditResult()
    {
        $this->handleImport('first', '1/1/2021');

        /** @var RaceResult $result */
        $result = $this->entityManager->getRepository(RaceResult::class)->findOneBy(['fullName' => 'Toby Phillips']);
        self::assertNotEmpty($result);

        $values = [
            'full_name' => 'jimmy stanic',
            'time' => '01:11:11',
            'distance' => RaceResult::DISTANCE_MEDIUM,
            'age_category' => 'M18-25',
        ];

        $this->raceService->editResult($result, $values);
        $this->entityManager->refresh($result);
        self::assertEquals($values['full_name'], $result->getFullName());
        self::assertEquals(($values['time']), $result->getTime()->format('h:i:s'));
        self::assertEquals($values['distance'], $result->getDistance());
        self::assertEquals($values['age_category'], $result->getAgeCategory());
    }

    public function testGetRaces() {
        $this->handleImport('first', '1/1/2021');
        $this->handleImport('second', '2/2/2022');

        $results = $this->raceService->getRaces();
        self::assertCount(2, $results);
        assertTrue(isset($results[0]['id']));
        assertTrue(isset($results[0]['title']));
    }

    private function handleImport(string $title, string $date) {
        return $this->raceService->importFromFile($title, $date,__DIR__."/data.csv");
    }

}