<?php

namespace App\Tests;

use App\Entity\Admin;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LoginControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    /**
     * Runs before each test.
     */
    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = static::getContainer();
        $em = $container->get('doctrine.orm.entity_manager');
        $userRepository = $em->getRepository(Admin::class);

        foreach ($userRepository->findAll() as $user) {
            $em->remove($user);
        }

        $em->flush();

        $passwordHasher = $container->get('security.user_password_hasher');

        $user = new Admin()->setEmail('email@example.com');
        $user->setPassword($passwordHasher->hashPassword($user, 'password'));
        $user->setRoles(['ROLE_USER']);
        $em->persist($user);

        $activeUser = new Admin()->setEmail('active@example.com');
        $activeUser->setPassword($passwordHasher->hashPassword($user, 'password'));
        $activeUser->setRoles(['ROLE_USER']);
        $activeUser->setIsActive(true);

        $em->persist($activeUser);
        $em->flush();
    }

    /**
     * Test login functionality.
     */
    public function testLogin(): void
    {
        // Denied - Can't login with invalid email address.
        $this->client->request('GET', '/login');
        self::assertResponseIsSuccessful();

        $this->client->submitForm(
            'Sign in',
            [
                '_username' => 'doesNotExist@example.com',
                '_password' => 'password',
            ]
        );

        self::assertResponseRedirects('/login');
        $this->client->followRedirect();

        // Ensure we do not reveal if the user exists or not.
        self::assertSelectorTextContains('.alert-danger', 'Invalid credentials.');

        // Denied - Can't login with invalid password.
        $this->client->request('GET', '/login');
        self::assertResponseIsSuccessful();

        $this->client->submitForm(
            'Sign in',
            [
                '_username' => 'email@example.com',
                '_password' => 'bad-password',
            ]
        );

        self::assertResponseRedirects('/login');
        $this->client->followRedirect();

        // Ensure we do not reveal the user exists but the password is wrong.
        self::assertSelectorTextContains('.error', 'Your account has not yet been activated.');

        // Success - Login with valid credentials is allowed.
        $this->client->submitForm(
            'Sign in',
            [
                '_username' => 'active@example.com',
                '_password' => 'password',
            ]
        );

        $crawler = $this->client->followRedirect();

        self::assertSelectorNotExists('.error');
        self::assertSelectorTextContains('.success', 'You have successfully logged in!');
        self::assertResponseIsSuccessful();
    }
}
