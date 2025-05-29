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

    /**
     * Runs before each test.
     */
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

        $passwordHasher = $container->get('security.user_password_hasher');

        $this->adminPassword = 'CyJsfuJ5IbXuXSMxuIVe6T2Lt';
        $this->adminEmail = 'superadmin@example.com';

        $superAdmin = new Admin()->setEmail($this->adminEmail);
        $superAdmin->setPassword($passwordHasher->hashPassword($superAdmin, $this->adminPassword));
        $superAdmin->setRoles(['ROLE_USER', 'ROLE_SUPER_ADMIN']);
        $superAdmin->setIsActive(true);

        $this->em->persist($superAdmin);
        $this->em->flush();
    }

    /**
     * Test showin a list of admins.
     */
    public function testShowAll(): void
    {
        $admin1 = new Admin()->setEmail('admin1@example.com')->setPassword('password')->setRoles(['ROLE_USER'])->setIsActive(true);
        $admin2 = new Admin()->setEmail('admin2@example.com')->setPassword('password')->setRoles(['ROLE_USER'])->setIsActive(false);
        $this->em->persist($admin1);
        $this->em->persist($admin2);
        $this->em->flush();

        $this->login();

        $crawler = $this->client->request('GET', '/admin');

        self::assertStringContainsString('Administrate Users', $crawler->html());
    }

    /**
     * Test activating a user/admin.
     */
    public function testActivateUserSuccess(): void
    {
        $admin = new Admin()
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

    /**
     * Test activating an already active user.
     */
    public function testCannotActivateAlreadyActiveUser(): void
    {
        $admin = new Admin()->setEmail('test@example.com')->setPassword('password')->setRoles(['ROLE_USER'])->setIsActive(true);
        $this->em->persist($admin);
        $this->em->flush();
        $adminId = $admin->getId();

        $this->login();

        $this->client->request('GET', '/admin');
        $this->client->request('POST', "/admin/activate/user/$adminId");

        self::assertResponseStatusCodeSame(500);
    }

    /**
     * Test deleting a user.
     */
    public function testDeleteUserSuccess(): void
    {
        $admin = new Admin()->setEmail('test@example.com')->setPassword('password')->setRoles(['ROLE_USER'])->setIsActive(true);
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

    /**
     * Test trying to delete a non existent user.
     */
    public function testCannotDeleteNonExistentUser(): void
    {
        $this->login();

        $this->client->request('GET', '/admin');
        $this->client->request('POST', '/admin/activate/user/12345');

        self::assertResponseStatusCodeSame(500);
    }

    /**
     * Test that a super admin user can not be deleted.
     * If the superadmin could be deleted that would mean
     * the superadmin could delete their own account.
     */
    public function testCannotDeleteSuperAdminUser(): void
    {
        $admin = new Admin()->setEmail('newsuperadmin@example.com')->setPassword('password')->setRoles(['ROLE_SUPER_ADMIN'])->setIsActive(true);
        $this->em->persist($admin);
        $this->em->flush();
        $adminId = $admin->getId();

        $this->login();
        $this->client->request('POST', "/admin/delete/user/$adminId");

        self::assertResponseStatusCodeSame(500);
    }

    /**
     * Helper function for logging in.
     */
    protected function login(): void
    {
        $this->client->request('GET', '/login');
        self::assertResponseIsSuccessful();

        $email = $this->adminEmail;
        $password = $this->adminPassword;
        $this->client->submitForm(
            'Sign in',
            [
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
