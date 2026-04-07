<?php

namespace App\Tests\Controller;

use App\Entity\Destination;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomeControllerTest extends WebTestCase
{
    public function testHomePageLoads(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('h1');
    }

    public function testHomePageShowsDestinations(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $em */
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $destination = new Destination();
        $destination->setName('Paris');
        $destination->setDescription('3 nights in a hotel');
        $destination->setPrice(100.0);
        $destination->setDuration('7 days');
        $destination->setImage('https://example.com/paris.jpg');

        $em->persist($destination);
        $em->flush();

        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Paris');

        $em->remove($destination);
        $em->flush();
    }

    public function testDestinationDetailPage(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $em */
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $destination = new Destination();
        $destination->setName('Tunis');
        $destination->setDescription('10 nights in a villa with a swimming pool');
        $destination->setPrice(200.0);
        $destination->setDuration('17 days');
        $destination->setImage('https://example.com/tunis.jpg');

        $em->persist($destination);
        $em->flush();

        $client->request('GET', '/destination/' . $destination->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Tunis');

        $em->remove($destination);
        $em->flush();
    }

    public function testAdminRedirectsToLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/destinations');

        $this->assertResponseRedirects('/login');
    }
}
