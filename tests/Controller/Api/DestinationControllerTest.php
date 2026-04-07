<?php

namespace App\Tests\Controller\Api;

use App\Entity\Destination;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DestinationControllerTest extends WebTestCase
{
    private function createDestination(EntityManagerInterface $em, string $name = 'Paris'): Destination
    {
        $destination = new Destination();
        $destination->setName($name);
        $destination->setDescription('A lovely destination');
        $destination->setPrice(100.0);
        $destination->setDuration('7 days');
        $destination->setImage('https://example.com/image.jpg');

        $em->persist($destination);
        $em->flush();

        return $destination;
    }

    public function testApiListReturnsJson(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/destinations');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
    }

    public function testApiListReturnsDestinations(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $em */
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $destination = $this->createDestination($em, 'Rome');

        $client->request('GET', '/api/destinations');

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data);

        $names = array_column($data, 'name');
        $this->assertContains('Rome', $names);

        $em->remove($destination);
        $em->flush();
    }

    public function testApiFilterByName(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $em */
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $paris = $this->createDestination($em, 'Paris');
        $tokyo = $this->createDestination($em, 'Tokyo');

        $client->request('GET', '/api/destinations?name=Paris');

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertResponseIsSuccessful();

        $names = array_column($data, 'name');
        $this->assertContains('Paris', $names);
        $this->assertNotContains('Tokyo', $names);

        $em->remove($paris);
        $em->remove($tokyo);
        $em->flush();
    }

    public function testApiShowSingleDestination(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $em */
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $destination = $this->createDestination($em, 'Barcelona');

        $client->request('GET', '/api/destinations/' . $destination->getId());

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('Barcelona', $data['name']);
        $this->assertArrayHasKey('price', $data);
        $this->assertArrayHasKey('duration', $data);

        $em->remove($destination);
        $em->flush();
    }

    public function testApiShowReturns404ForUnknown(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/destinations/99999');

        $this->assertResponseStatusCodeSame(404);
    }
}
