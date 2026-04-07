<?php

namespace App\Tests\Security;

use App\DataFixtures\UserFixtures;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SecurityTest extends WebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        static::bootKernel();
        static::getContainer()->get(EntityManagerInterface::class)
            ->createQuery('DELETE FROM App\Entity\User')
            ->execute();
        static::ensureKernelShutdown();
    }

    protected function tearDown(): void
    {
        static::bootKernel();
        static::getContainer()->get(EntityManagerInterface::class)
            ->createQuery('DELETE FROM App\Entity\User')
            ->execute();
        parent::tearDown(); // calls ensureKernelShutdown internally
    }

    private function createAdmin(): User
    {
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $user = new User();
        $user->setEmail(UserFixtures::ADMIN_EMAIL);
        $user->setRoles(['ROLE_ADMIN']);
        $user->setPassword($hasher->hashPassword($user, UserFixtures::ADMIN_PASSWORD));

        $em->persist($user);
        $em->flush();

        return $user;
    }

    private function createCustomer(): User
    {
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $user = new User();
        $user->setEmail(UserFixtures::CUSTOMER_EMAIL);
        $user->setRoles([]);
        $user->setPassword($hasher->hashPassword($user, UserFixtures::CUSTOMER_PASSWORD));

        $em->persist($user);
        $em->flush();

        return $user;
    }

    // --- Login page ---

    public function testLoginPageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('input[name="_username"]');
        $this->assertSelectorExists('input[name="_password"]');
    }

    // --- Unauthenticated access ---

    public function testUnauthenticatedUserIsRedirectedToHomeFromAdmin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/destinations');

        $this->assertResponseRedirects('/');
        $client->followRedirect();
        $this->assertSelectorExists('.flash-error, [class*="bg-red"]');
    }

    // --- Admin login & access ---

    public function testAdminCanLoginAndAccessBackoffice(): void
    {
        $client = static::createClient();
        $this->createAdmin();

        $client->request('GET', '/login');
        $client->submitForm('Sign in', [
            '_username' => UserFixtures::ADMIN_EMAIL,
            '_password' => UserFixtures::ADMIN_PASSWORD,
        ]);

        $this->assertResponseRedirects('/admin/destinations');
        $client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Destinations');
    }

    public function testCustomerIsRedirectedToHomeAfterLogin(): void
    {
        $client = static::createClient();
        $this->createCustomer();

        $client->request('GET', '/login');
        $client->submitForm('Sign in', [
            '_username' => UserFixtures::CUSTOMER_EMAIL,
            '_password' => UserFixtures::CUSTOMER_PASSWORD,
        ]);

        $this->assertResponseRedirects('/');
    }

    public function testAdminCanAccessNewDestinationPage(): void
    {
        $client = static::createClient();
        $admin = $this->createAdmin();

        $client->loginUser($admin);
        $client->request('GET', '/admin/destinations/new');

        $this->assertResponseIsSuccessful();
    }

    // --- Customer access ---

    public function testCustomerIsRedirectedToHomeFromAdmin(): void
    {
        $client = static::createClient();
        $customer = $this->createCustomer();

        $client->loginUser($customer);
        $client->request('GET', '/admin/destinations');

        $this->assertResponseRedirects('/');
        $client->followRedirect();
        $this->assertSelectorExists('[class*="bg-red"]');
    }

    public function testCustomerCanAccessHomePage(): void
    {
        $client = static::createClient();
        $customer = $this->createCustomer();

        $client->loginUser($customer);
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
    }

    public function testCustomerCanAccessPublicApi(): void
    {
        $client = static::createClient();
        $customer = $this->createCustomer();

        $client->loginUser($customer);
        $client->request('GET', '/api/destinations');

        $this->assertResponseIsSuccessful();
    }

    // --- Failed login ---

    public function testLoginWithWrongPasswordFails(): void
    {
        $client = static::createClient();
        $this->createAdmin();

        $client->request('GET', '/login');
        $client->submitForm('Sign in', [
            '_username' => UserFixtures::ADMIN_EMAIL,
            '_password' => 'wrong_password',
        ]);

        $client->followRedirect();
        $this->assertSelectorExists('#error-message');
    }

    public function testLoginWithUnknownEmailFails(): void
    {
        $client = static::createClient();

        $client->request('GET', '/login');
        $client->submitForm('Sign in', [
            '_username' => 'nobody@foo-trip.com',
            '_password' => 'whatever',
        ]);

        $client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('app_login');
    }

    // --- Logout ---

    public function testAdminCanLogout(): void
    {
        $client = static::createClient();
        $admin = $this->createAdmin();

        $client->loginUser($admin);
        $client->request('GET', '/logout');

        $this->assertResponseRedirects();
        $client->followRedirect();

        $client->request('GET', '/admin/destinations');
        $this->assertResponseRedirects('/');
    }
}
