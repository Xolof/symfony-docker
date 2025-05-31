<?php

namespace App\DataFixtures;

use App\Entity\Admin;
use App\Entity\Badger;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;

class AppFixtures extends Fixture
{
    /**
     * Insert fake entities into the database
     */
    public function load(ObjectManager $manager): void
    {
        $faker = FakerFactory::create();

        echo "Creating fake Badgers...\n";

        for ($i = 0; $i < 100; $i++) {
            $badger = new Badger();
            $badger->setName($faker->name());
            $badger->setContinent($faker->name());
            $badger->setDescription($faker->realText(1200));
            $badger->setImageFileName("default.jpg");

            $manager->persist($badger);
            $manager->flush();
        }

        echo "Creating fake Users...\n";

        for ($i = 0; $i < 100; $i++) {
            $password = implode('', $faker->randomElements(range('A', 'z'), 20));

            $user = new Admin();

            $user->setPassword($password);
            $user->setEmail($faker->email());

            $manager->persist($user);
            $manager->flush();
        }
    }
}
