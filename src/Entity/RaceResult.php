<?php

namespace App\Entity;

use App\Repository\RaceResultRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity(repositoryClass: RaceResultRepository::class)]
#[ORM\Table(name: 'race_results')]
class RaceResult
{
    const DISTANCE_MEDIUM = 'medium';
    const DISTANCE_LONG = 'long';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(name: 'full_name')]
    private string $fullName;

    #[ORM\Column]
    private string $distance;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private \DateTime $time;

    #[ORM\Column(name: 'age_category')]
    private string $ageCategory;

    #[ORM\ManyToOne(targetEntity: Race::class, inversedBy: 'raceResults')]
    #[ORM\JoinColumn(name: 'race_id', referencedColumnName: 'id')]
    private Race $race;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getFullName(): string
    {
        return $this->fullName;
    }

    /**
     * @param string $fullName
     */
    public function setFullName(string $fullName): void
    {
        $this->fullName = $fullName;
    }

    /**
     * @return string
     */
    public function getDistance(): string
    {
        return $this->distance;
    }

    /**
     * @param string $distance
     */
    public function setDistance(string $distance): void
    {
        $this->distance = $distance;
    }

    /**
     * @return \DateTime
     */
    public function getTime(): \DateTime
    {
        return $this->time;
    }

    /**
     * @param \DateTime $time
     */
    public function setTime(\DateTime $time): void
    {
        $this->time = $time;
    }

    /**
     * @return string
     */
    public function getAgeCategory(): string
    {
        return $this->ageCategory;
    }

    /**
     * @param string $ageCategory
     */
    public function setAgeCategory(string $ageCategory): void
    {
        $this->ageCategory = $ageCategory;
    }

    /**
     * @return Race
     */
    public function getRace(): Race
    {
        return $this->race;
    }

    /**
     * @param Race $race
     */
    public function setRace(Race $race): void
    {
        $this->race = $race;
    }
}