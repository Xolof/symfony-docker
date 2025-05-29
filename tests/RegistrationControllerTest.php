<?php

namespace App\Tests\Controller;

use App\Entity\Admin;
use App\Repository\AdminRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationControllerTest extends WebTestCase
{
    protected KernelBrowser $client;

    protected EntityManagerInterface $em;

    protected AdminRepository $adminRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $container = static::getContainer();
        $this->em = $container->get('doctrine')->getManager();
        $this->adminRepository = $container->get(AdminRepository::class);

        // Clean the admin table
        foreach ($this->adminRepository->findAll() as $admin) {
            $this->em->remove($admin);
        }
        $this->em->flush();
    }

    public function test_register_success(): void
    {
        $crawler = $this->client->request('GET', '/register');
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Register a new user', $crawler->html());

        $form = $crawler->selectButton('Register')->form([
            'form[email]' => 'test@example.com',
            'form[password]' => 'StrongPassword123',
        ]);

        $this->client->submit($form);

        self::assertResponseRedirects('/login');
        $this->client->followRedirect();
        self::assertSelectorTextContains('.success', 'Your account has been successfully created.');

        $admin = $this->adminRepository->findOneBy(['email' => 'test@example.com']);
        self::assertNotNull($admin);
        self::assertEquals(['ROLE_USER'], $admin->getRoles());

        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        self::assertTrue($passwordHasher->isPasswordValid($admin, 'StrongPassword123'));
    }

    public function test_register_with_invalid_email(): void
    {
        $crawler = $this->client->request('GET', '/register');
        self::assertResponseIsSuccessful();

        $email = 'invalid-email';

        $form = $crawler->selectButton('Register')->form([
            'form[email]' => $email,
            'form[password]' => 'StrongPassword123',
        ]);

        $crawler = $this->client->submit($form);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.error', "The email \"$email\" is not a valid email.");
        self::assertStringContainsString('Register a new user', $crawler->html());
        self::assertEmpty($this->adminRepository->findAll());
    }

    public function test_register_with_invalid_password(): void
    {
        $crawler = $this->client->request('GET', '/register');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Register')->form([
            'form[email]' => 'test@example.com',
            'form[password]' => 'weak',
        ]);

        $crawler = $this->client->submit($form);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.error', 'The password strength is too low. Please use a stronger password.');
        self::assertStringContainsString('Register a new user', $crawler->html());
        self::assertEmpty($this->adminRepository->findAll());
    }

    public function test_register_with_existing_email(): void
    {
        $email = 'test@example.com';
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $admin = (new Admin)
            ->setEmail($email)
            ->setPassword($passwordHasher->hashPassword(new Admin, 'StrongPassword123'))
            ->setRoles(['ROLE_USER']);
        $this->em->persist($admin);
        $this->em->flush();

        $crawler = $this->client->request('GET', '/register');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Register')->form([
            'form[email]' => $email,
            'form[password]' => 'StrongPassword123',
        ]);

        $crawler = $this->client->submit($form);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.error', "The email \"$email\" is already in use.");
        self::assertStringContainsString('Register a new user', $crawler->html());
        self::assertCount(1, $this->adminRepository->findAll());
    }
}
