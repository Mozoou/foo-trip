<?php

namespace App\Tests\DataFixtures;

use App\DataFixtures\DestinationFixtures;
use App\Entity\Destination;
use App\Repository\DestinationRepository;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DestinationFixturesTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private DestinationRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->repository = static::getContainer()->get(DestinationRepository::class);

        $this->loadFixtures();
    }

    private function loadFixtures(): void
    {
        $this->em->createQuery('DELETE FROM App\Entity\Destination')->execute();

        $fixtures = new DestinationFixtures();
        $fixtures->setReferenceRepository(new ReferenceRepository($this->em));
        $fixtures->load($this->em);
    }

    public function testFixturesLoadCorrectNumberOfDestinations(): void
    {
        $this->assertCount(5, $this->repository->findAll());
    }

    public function testFixturesDestinationsHaveRequiredFields(): void
    {
        foreach ($this->repository->findAll() as $destination) {
            $this->assertNotEmpty($destination->getName());
            $this->assertNotEmpty($destination->getDescription());
            $this->assertGreaterThan(0, $destination->getPrice());
            $this->assertNotEmpty($destination->getDuration());
            $this->assertNotEmpty($destination->getImage());
        }
    }

    public function testFixturesContainExpectedDestinations(): void
    {
        $names = array_map(
            fn(Destination $d) => $d->getName(),
            $this->repository->findAll()
        );

        $this->assertContains('Paris', $names);
        $this->assertContains('Tunis', $names);
        $this->assertContains('Bali', $names);
        $this->assertContains('Santorini', $names);
        $this->assertContains('Maldives', $names);
    }

    public function testFixturesPricesArePositive(): void
    {
        foreach ($this->repository->findAll() as $destination) {
            $this->assertIsFloat($destination->getPrice());
            $this->assertGreaterThan(0.0, $destination->getPrice());
        }
    }

    public function testFixturesReferencesAreAvailable(): void
    {
        $this->em->createQuery('DELETE FROM App\Entity\Destination')->execute();

        $fixtures = new DestinationFixtures();
        $refRepo = new ReferenceRepository($this->em);
        $fixtures->setReferenceRepository($refRepo);
        $fixtures->load($this->em);

        $paris = $fixtures->getReference('destination_0', Destination::class);
        $this->assertSame('Paris', $paris->getName());

        $maldives = $fixtures->getReference('destination_4', Destination::class);
        $this->assertSame('Maldives', $maldives->getName());
    }

    protected function tearDown(): void
    {
        $this->em->createQuery('DELETE FROM App\Entity\Destination')->execute();
        parent::tearDown();
    }
}
