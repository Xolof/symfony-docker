<?php

namespace App\Tests;

use App\Entity\Admin;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LoginControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = static::getContainer();
        $em = $container->get('doctrine.orm.entity_manager');
        $userRepository = $em->getRepository(Admin::class);

        // Remove any existing users from the test database
        foreach ($userRepository->findAll() as $user) {
            $em->remove($user);
        }

        $em->flush();

        // Create a Admin fixture
        /**
         * @var UserPasswordHasherInterface $passwordHasher
        */
        $passwordHasher = $container->get('security.user_password_hasher');

        $user = (new Admin())->setEmail('email@example.com');
        $user->setPassword($passwordHasher->hashPassword($user, 'password'));
        $em->persist($user);

        $activeUser = (new Admin())->setEmail('active@example.com');
        $activeUser->setPassword($passwordHasher->hashPassword($user, 'password'));
        $activeUser->setIsActive(true);
        $em->persist($user);

        $em->flush();
    }

    public function testLogin(): void
    {
        // Denied - Can't login with invalid email address.
        $this->client->request('GET', '/login');
        self::assertResponseIsSuccessful();

        $this->client->submitForm(
            'Sign in', [
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
            'Sign in', [
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
            'Sign in', [
            '_username' => 'active@example.com',
            '_password' => 'password',
            ]
        );

        // self::assertResponseRedirects('/');
        $this->client->followRedirect();

        self::assertSelectorNotExists('.error');
        self::assertResponseIsSuccessful();
    }
}
