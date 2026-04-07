<?php

namespace App\Tests\DataFixtures;

use App\DataFixtures\UserFixtures;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixturesTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private UserRepository $repository;
    private UserPasswordHasherInterface $hasher;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->repository = static::getContainer()->get(UserRepository::class);
        $this->hasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $this->loadFixtures();
    }

    private function loadFixtures(): void
    {
        $this->em->createQuery('DELETE FROM App\Entity\User')->execute();

        $fixtures = new UserFixtures($this->hasher);
        $fixtures->setReferenceRepository(new ReferenceRepository($this->em));
        $fixtures->load($this->em);
    }

    public function testFixturesLoadTwoUsers(): void
    {
        $this->assertCount(2, $this->repository->findAll());
    }

    public function testAdminUserExists(): void
    {
        $admin = $this->repository->findOneBy(['email' => UserFixtures::ADMIN_EMAIL]);

        $this->assertNotNull($admin);
        $this->assertContains('ROLE_ADMIN', $admin->getRoles());
        $this->assertContains('ROLE_USER', $admin->getRoles());
    }

    public function testCustomerUserExists(): void
    {
        $customer = $this->repository->findOneBy(['email' => UserFixtures::CUSTOMER_EMAIL]);

        $this->assertNotNull($customer);
        $this->assertContains('ROLE_USER', $customer->getRoles());
        $this->assertNotContains('ROLE_ADMIN', $customer->getRoles());
    }

    public function testAdminPasswordIsHashed(): void
    {
        $admin = $this->repository->findOneBy(['email' => UserFixtures::ADMIN_EMAIL]);

        $this->assertNotSame(UserFixtures::ADMIN_PASSWORD, $admin->getPassword());
        $this->assertTrue($this->hasher->isPasswordValid($admin, UserFixtures::ADMIN_PASSWORD));
    }

    public function testCustomerPasswordIsHashed(): void
    {
        $customer = $this->repository->findOneBy(['email' => UserFixtures::CUSTOMER_EMAIL]);

        $this->assertNotSame(UserFixtures::CUSTOMER_PASSWORD, $customer->getPassword());
        $this->assertTrue($this->hasher->isPasswordValid($customer, UserFixtures::CUSTOMER_PASSWORD));
    }

    public function testReferencesAreAvailable(): void
    {
        $this->em->createQuery('DELETE FROM App\Entity\User')->execute();

        $fixtures = new UserFixtures($this->hasher);
        $refRepo = new ReferenceRepository($this->em);
        $fixtures->setReferenceRepository($refRepo);
        $fixtures->load($this->em);

        $admin = $fixtures->getReference(UserFixtures::ADMIN_REFERENCE, User::class);
        $this->assertSame(UserFixtures::ADMIN_EMAIL, $admin->getEmail());

        $customer = $fixtures->getReference(UserFixtures::CUSTOMER_REFERENCE, User::class);
        $this->assertSame(UserFixtures::CUSTOMER_EMAIL, $customer->getEmail());
    }

    protected function tearDown(): void
    {
        $this->em->createQuery('DELETE FROM App\Entity\User')->execute();
        parent::tearDown();
    }
}
