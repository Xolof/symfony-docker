<?php

namespace App\Tests\Controller;

use App\Entity\Admin;
use App\Repository\AdminRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminControllerTest extends WebTestCase
{
    protected KernelBrowser $client;

    protected EntityManagerInterface $em;

    protected AdminRepository $adminRepository;

    protected Admin $superAdmin;

    protected string $adminPassword;

    protected string $adminEmail;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $container = static::getContainer();
        $this->em = $container->get('doctrine')->getManager();
        $this->adminRepository = $container->get(AdminRepository::class);

        foreach ($this->adminRepository->findAll() as $admin) {
            $this->em->remove($admin);
        }
        $this->em->flush();

        /**
         * @var UserPasswordHasherInterface $passwordHasher
         */
        $passwordHasher = $container->get('security.user_password_hasher');

        $this->adminPassword = 'CyJsfuJ5IbXuXSMxuIVe6T2Lt';
        $this->adminEmail = 'superadmin@example.com';

        $superAdmin = (new Admin)->setEmail($this->adminEmail);
        $superAdmin->setPassword($passwordHasher->hashPassword($superAdmin, $this->adminPassword));
        $superAdmin->setRoles(['ROLE_USER', 'ROLE_SUPER_ADMIN']);
        $superAdmin->setIsActive(true);

        $this->em->persist($superAdmin);
        $this->em->flush();
    }

    public function test_show_all(): void
    {
        $admin1 = (new Admin)->setEmail('admin1@example.com')->setPassword('password')->setRoles(['ROLE_USER'])->setIsActive(true);
        $admin2 = (new Admin)->setEmail('admin2@example.com')->setPassword('password')->setRoles(['ROLE_USER'])->setIsActive(false);
        $this->em->persist($admin1);
        $this->em->persist($admin2);
        $this->em->flush();

        $this->login();

        $crawler = $this->client->request('GET', '/admin');

        self::assertStringContainsString('Administrate Users', $crawler->html());
    }

    public function test_activate_user_success(): void
    {
        $admin = (new Admin)
            ->setEmail('test@example.com')
            ->setPassword('password')
            ->setRoles(['ROLE_USER'])
            ->setIsActive(false);

        $this->em->persist($admin);
        $this->em->flush();
        $adminId = $admin->getId();

        $this->login();

        $crawler = $this->client->request('GET', '/admin');
        $form = $crawler->selectButton('Activate user')->form();

        $this->client->submit($form);

        self::assertEmailCount(1);
        $messages = $this->getMailerMessages();
        self::assertCount(1, $messages);
        self::assertEmailAddressContains($messages[0], 'to', 'test@example.com');
        self::assertEmailAddressContains($messages[0], 'from', 'admin@localhost');
        self::assertEmailHtmlBodyContains($messages[0], 'Your account has been activated');

        $updatedAdmin = $this->adminRepository->find($adminId);
        self::assertTrue($updatedAdmin->isActive());
    }

    public function test_cannot_activate_already_active_user(): void
    {
        $admin = (new Admin)->setEmail('test@example.com')->setPassword('password')->setRoles(['ROLE_USER'])->setIsActive(true);
        $this->em->persist($admin);
        $this->em->flush();
        $adminId = $admin->getId();

        $this->login();

        $this->client->request('GET', '/admin');
        $this->client->request('POST', "/admin/activate/user/$adminId");

        self::assertResponseStatusCodeSame(500);
    }

    public function test_delete_user_success(): void
    {
        $admin = (new Admin)->setEmail('test@example.com')->setPassword('password')->setRoles(['ROLE_USER'])->setIsActive(true);
        $this->em->persist($admin);
        $this->em->flush();
        $adminId = $admin->getId();

        $this->login();

        $crawler = $this->client->request('GET', '/admin');

        $form = $crawler->selectButton('Delete user')->form();
        $this->client->submit($form);

        self::assertResponseRedirects('/admin');
        $this->client->followRedirect();

        self::assertSelectorTextContains('.success', 'User deleted!');
        $deletedAdmin = $this->adminRepository->find($adminId);
        self::assertNull($deletedAdmin);
    }

    public function test_cannot_delete_non_existent_user(): void
    {
        $this->login();

        $this->client->request('GET', '/admin');
        $this->client->request('POST', '/admin/activate/user/12345');

        self::assertResponseStatusCodeSame(500);
    }

    public function test_cannot_delete_super_admin_user(): void
    {
        $admin = (new Admin)->setEmail('newsuperadmin@example.com')->setPassword('password')->setRoles(['ROLE_SUPER_ADMIN'])->setIsActive(true);
        $this->em->persist($admin);
        $this->em->flush();
        $adminId = $admin->getId();

        $this->login();
        $this->client->request('POST', "/admin/delete/user/$adminId");

        self::assertResponseStatusCodeSame(500);
    }

    protected function login(): void
    {
        $this->client->request('GET', '/login');
        self::assertResponseIsSuccessful();

        $email = $this->adminEmail;
        $password = $this->adminPassword;
        $this->client->submitForm(
            'Sign in', [
                '_username' => $email,
                '_password' => $password,
            ]
        );

        $this->client->followRedirect();

        self::assertSelectorTextContains('.success', 'You have successfully logged in!');
        self::assertSelectorNotExists('.error');
        self::assertResponseIsSuccessful();
    }
}
