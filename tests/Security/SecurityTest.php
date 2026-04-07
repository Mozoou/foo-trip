<?php

namespace App\Tests\Security;

use App\DataFixtures\UserFixtures;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SecurityTest extends WebTestCase
{
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

    private function cleanUsers(): void
    {
        static::getContainer()->get(EntityManagerInterface::class)
            ->createQuery('DELETE FROM App\Entity\User')
            ->execute();
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

    public function testUnauthenticatedUserIsRedirectedFromAdmin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/destinations');

        $this->assertResponseRedirects('/login');
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
        $this->assertSelectorTextContains('h1', 'Manage Destinations');

        $this->cleanUsers();
    }

    public function testAdminCanAccessNewDestinationPage(): void
    {
        $client = static::createClient();
        $admin = $this->createAdmin();

        $client->loginUser($admin);
        $client->request('GET', '/admin/destinations/new');

        $this->assertResponseIsSuccessful();

        $this->cleanUsers();
    }

    // --- Customer access ---

    public function testCustomerCannotAccessAdminArea(): void
    {
        $client = static::createClient();
        $customer = $this->createCustomer();

        $client->loginUser($customer);
        $client->request('GET', '/admin/destinations');

        $this->assertResponseStatusCodeSame(403);

        $this->cleanUsers();
    }

    public function testCustomerCanAccessHomePage(): void
    {
        $client = static::createClient();
        $customer = $this->createCustomer();

        $client->loginUser($customer);
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();

        $this->cleanUsers();
    }

    public function testCustomerCanAccessPublicApi(): void
    {
        $client = static::createClient();
        $customer = $this->createCustomer();

        $client->loginUser($customer);
        $client->request('GET', '/api/destinations');

        $this->assertResponseIsSuccessful();

        $this->cleanUsers();
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
        $this->assertSelectorExists('.flash-error, #error-message, [class*="alert"], [class*="error"]');

        $this->cleanUsers();
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

        $this->cleanUsers();
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

        // After logout, /admin should redirect to login
        $client->request('GET', '/admin/destinations');
        $this->assertResponseRedirects('/login');

        $this->cleanUsers();
    }
}
