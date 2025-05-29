<?php

namespace App\Tests\Controller;

use App\Entity\Admin;
use App\Entity\Badger;
use App\Repository\BadgerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class BadgerControllerTest extends WebTestCase
{
    protected KernelBrowser $client;

    protected EntityManagerInterface $em;

    protected BadgerRepository $badgerRepository;

    protected Admin $superAdmin;

    protected string $adminPassword;

    protected string $adminEmail;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $container = static::getContainer();
        $this->em = $container->get('doctrine')->getManager();
        $this->badgerRepository = $container->get(BadgerRepository::class);

        foreach ($this->badgerRepository->findAll() as $badger) {
            $this->em->remove($badger);
        }

        $adminRepository = $container->get(\App\Repository\AdminRepository::class);
        foreach ($adminRepository->findAll() as $admin) {
            $this->em->remove($admin);
        }
        $this->em->flush();

        $passwordHasher = $container->get(UserPasswordHasherInterface::class);
        $this->adminPassword = 'CyJsfuJ5IbXuXSMxuIVe6T2Lt';
        $this->adminEmail = 'superadmin@example.com';

        $this->superAdmin = (new Admin)->setEmail($this->adminEmail);
        $this->superAdmin->setPassword($passwordHasher->hashPassword($this->superAdmin, $this->adminPassword));
        $this->superAdmin->setRoles(['ROLE_USER', 'ROLE_SUPER_ADMIN']);
        $this->superAdmin->setIsActive(true);

        $this->em->persist($this->superAdmin);
        $this->em->flush();
    }

    public function test_show(): void
    {
        $badger = (new Badger)
            ->setName('Honey Badger')
            ->setContinent('Africa')
            ->setDescription('Fearless creature');
        $this->em->persist($badger);
        $this->em->flush();
        $badgerId = $badger->getId();

        $this->login();

        $crawler = $this->client->request('GET', "/badger/$badgerId");

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Honey Badger', $crawler->html());
        self::assertStringContainsString('Africa', $crawler->html());
        self::assertStringContainsString('Fearless creature', $crawler->html());
    }

    public function test_show_non_existent_badger(): void
    {
        $this->login();

        $this->client->request('GET', '/badger/999');

        self::assertResponseStatusCodeSame(404);
        self::assertStringContainsString('No badger found for id 999', $this->client->getResponse()->getContent());
    }

    public function test_show_all(): void
    {
        $badger1 = (new Badger)->setName('Honey Badger')->setContinent('Africa')->setDescription('Fearless');
        $badger2 = (new Badger)->setName('European Badger')->setContinent('Europe')->setDescription('Nocturnal');
        $this->em->persist($badger1);
        $this->em->persist($badger2);
        $this->em->flush();

        $this->login();

        $crawler = $this->client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Honey Badger', $crawler->html());
        self::assertStringContainsString('European Badger', $crawler->html());
    }

    public function test_create_badger_success(): void
    {
        $this->login();

        $crawler = $this->client->request('GET', '/create/badger');
        self::assertResponseIsSuccessful();

        $description = trim(<<<'EOD'
            Asian badgers (specifically the Hog Badger, though the term often applies to several species) are medium-sized mammals found across a wide range of habitats in Asia, from forests to grasslands.
            Appearance: They look like a stocky badger, often with a distinctive white stripe on their face and black legs.
        EOD);

        $form = $crawler->selectButton('Save Badger')->form([
            'form[name]' => 'Test Badger',
            'form[continent]' => 'Asia',
            'form[description]' => $description,
        ]);

        $crawler = $this->client->submit($form);

        self::assertResponseRedirects('/');
        $this->client->followRedirect();
        self::assertSelectorTextContains('.success', 'Your changes were saved!');

        $badger = $this->badgerRepository->findOneBy(['name' => 'Test Badger']);
        self::assertNotNull($badger);
        self::assertEquals('Asia', $badger->getContinent());
        self::assertEquals(trim($description), $badger->getDescription());
    }

    public function test_create_badger_with_invalid_input(): void
    {
        $this->login();

        // 1. Empty name
        $crawler = $this->client->request('GET', '/create/badger');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Save Badger')->form([
            'form[name]' => '',
            'form[continent]' => 'Asia',
            'form[description]' => 'A curious badger',
        ]);

        $crawler = $this->client->submit($form);

        self::assertResponseStatusCodeSame(500);

        // 2. Short description
        $crawler = $this->client->request('GET', '/create/badger');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Save Badger')->form([
            'form[name]' => 'Asian Badger',
            'form[continent]' => 'Asia',
            'form[description]' => 'A curious badger',
        ]);

        $crawler = $this->client->submit($form);

        self::assertSelectorTextContains('.error', 'This value is too short. It should have 100 characters or more.');
    }

    public function test_edit_badger_success(): void
    {
        $badger = (new Badger)
            ->setName('Honey Badger')
            ->setContinent('Africa')
            ->setDescription('Fearless');
        $this->em->persist($badger);
        $this->em->flush();
        $badgerId = $badger->getId();

        $this->login();

        $crawler = $this->client->request('GET', "/edit/badger/$badgerId");
        self::assertResponseIsSuccessful();

        $description = trim(<<<'EOD'
            This is a new description. Asian badgers (specifically the Hog Badger, though the term often applies to several species) are medium-sized mammals found across a wide range of habitats in Asia, from forests to grasslands.
            Appearance: They look like a stocky badger, often with a distinctive white stripe on their face and black legs.
        EOD);

        $form = $crawler->selectButton('Save Badger')->form([
            'form[name]' => 'Updated Badger',
            'form[continent]' => 'Asia',
            'form[description]' => $description,
        ]);

        $crawler = $this->client->submit($form);

        self::assertResponseRedirects("/edit/badger/$badgerId");
        $this->client->followRedirect();
        self::assertSelectorTextContains('.success', 'Your changes were saved!');

        $updatedBadger = $this->badgerRepository->find($badgerId);
        self::assertEquals('Updated Badger', $updatedBadger->getName());
        self::assertEquals('Asia', $updatedBadger->getContinent());
        self::assertEquals($description, $updatedBadger->getDescription());
    }

    public function test_edit_non_existent_badger(): void
    {
        $this->login();

        $this->client->request('GET', '/edit/badger/999');

        self::assertResponseStatusCodeSame(404);
        self::assertStringContainsString('No badger found for id 999', $this->client->getResponse()->getContent());
    }

    public function test_delete_badger_success(): void
    {
        $description = trim(<<<'EOD'
            This is a new description. Asian badgers (specifically the Hog Badger, though the term often applies to several species) are medium-sized mammals found across a wide range of habitats in Asia, from forests to grasslands.
            Appearance: They look like a stocky badger, often with a distinctive white stripe on their face and black legs.
        EOD);

        $badger = (new Badger)
            ->setName('Honey Badger')
            ->setContinent('Africa')
            ->setDescription($description);
        $this->em->persist($badger);
        $this->em->flush();
        $badgerId = $badger->getId();

        $this->login();

        $crawler = $this->client->request('GET', "/delete/badger/$badgerId");
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('I understand, delete this badger')->form();
        $crawler = $this->client->submit($form);

        self::assertResponseRedirects('/');
        $crawler = $this->client->followRedirect();

        self::assertSelectorTextContains('.success', 'Item deleted!');

        $deletedBadger = $this->badgerRepository->find($badgerId);
        self::assertNull($deletedBadger);
    }

    public function test_delete_non_existent_badger(): void
    {
        $this->login();

        $this->client->request('GET', '/delete/badger/999');

        self::assertResponseStatusCodeSame(404);
        self::assertStringContainsString('No badger found for id 999', $this->client->getResponse()->getContent());
    }

    protected function login(): void
    {
        $this->client->request('GET', '/login');
        self::assertResponseIsSuccessful();

        $this->client->submitForm('Sign in', [
            '_username' => $this->adminEmail,
            '_password' => $this->adminPassword,
        ]);

        $this->client->followRedirect();
        self::assertSelectorTextContains('.success', 'You have successfully logged in!');
        self::assertSelectorNotExists('.error');
        self::assertResponseIsSuccessful();
    }
}
