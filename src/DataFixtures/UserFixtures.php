<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public const ADMIN_REFERENCE = 'user_admin';
    public const CUSTOMER_REFERENCE = 'user_customer';

    public const ADMIN_EMAIL = 'admin@foo-trip.com';
    public const ADMIN_PASSWORD = 'admin1234';

    public const CUSTOMER_EMAIL = 'customer@foo-trip.com';
    public const CUSTOMER_PASSWORD = 'customer1234';

    public function __construct(
        private readonly UserPasswordHasherInterface $hasher,
    ) {}

    public function load(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setEmail(self::ADMIN_EMAIL);
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->hasher->hashPassword($admin, self::ADMIN_PASSWORD));
        $manager->persist($admin);
        $this->addReference(self::ADMIN_REFERENCE, $admin);

        $customer = new User();
        $customer->setEmail(self::CUSTOMER_EMAIL);
        $customer->setRoles([]);
        $customer->setPassword($this->hasher->hashPassword($customer, self::CUSTOMER_PASSWORD));
        $manager->persist($customer);
        $this->addReference(self::CUSTOMER_REFERENCE, $customer);

        $manager->flush();
    }
}
