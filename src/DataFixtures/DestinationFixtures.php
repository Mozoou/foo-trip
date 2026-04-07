<?php

namespace App\DataFixtures;

use App\Entity\Destination;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class DestinationFixtures extends Fixture
{
    private const DESTINATIONS = [
        [
            'name' => 'Paris',
            'description' => '3 nights in a luxury hotel in the heart of the city of love. Enjoy the Eiffel Tower, the Louvre, and world-class cuisine.',
            'price' => 100.0,
            'duration' => '7 days',
            'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/4b/La_Tour_Eiffel_vue_de_la_Tour_Saint-Jacques%2C_Paris_ao%C3%BBt_2014_%282%29.jpg/800px-La_Tour_Eiffel_vue_de_la_Tour_Saint-Jacques%2C_Paris_ao%C3%BBt_2014_%282%29.jpg',
        ],
        [
            'name' => 'Tunis',
            'description' => '10 nights in a villa with a private swimming pool. Discover the medina, ancient ruins, and stunning Mediterranean beaches.',
            'price' => 200.0,
            'duration' => '17 days',
            'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/a4/Tunis_wedigraf.jpg/800px-Tunis_wedigraf.jpg',
        ],
        [
            'name' => 'Bali',
            'description' => 'A romantic escape to the island of the gods. Stay in a private villa surrounded by rice terraces and tropical forests.',
            'price' => 350.0,
            'duration' => '10 days',
            'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/16/Bali_Indonesia_%28girl_offering%29.jpg/800px-Bali_Indonesia_%28girl_offering%29.jpg',
        ],
        [
            'name' => 'Santorini',
            'description' => 'Experience breathtaking sunsets over the Aegean Sea. Iconic white-washed buildings, volcanic beaches, and fine dining await.',
            'price' => 450.0,
            'duration' => '8 days',
            'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/3/3e/Santorini_-_Greece_-_panoramio.jpg/800px-Santorini_-_Greece_-_panoramio.jpg',
        ],
        [
            'name' => 'Maldives',
            'description' => 'Stay in an overwater bungalow surrounded by crystal-clear turquoise waters. Snorkel with manta rays and enjoy pure luxury.',
            'price' => 1200.0,
            'duration' => '14 days',
            'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/9/9e/Gili_Air_bungalows.jpg/800px-Gili_Air_bungalows.jpg',
        ],
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::DESTINATIONS as $index => $data) {
            $destination = new Destination();
            $destination->setName($data['name']);
            $destination->setDescription($data['description']);
            $destination->setPrice($data['price']);
            $destination->setDuration($data['duration']);
            $destination->setImage($data['image']);

            $manager->persist($destination);

            $this->addReference('destination_' . $index, $destination);
        }

        $manager->flush();
    }
}
