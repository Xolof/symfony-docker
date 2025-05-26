<?php

namespace App\DataFixtures;

use App\Entity\Badger;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 100; $i++) {
            $badger = new Badger;
            $badger->setName("Badger $i");
            $badger->setContinent("Continent $i");
            $badger->setDescription("The American Badger is a fascinating and often misunderstood creature. Here's a detailed breakdown of its characteristics, behavior, habitat, and conservation status: 1. Physical Characteristics: Size: Relatively stout and low to the ground. Length ranges from 23-35 inches (58-89 cm), including the tail. Weighs between 9-26 pounds (4-12 kg), with males being larger. Appearance: Recognizable by its distinctive markings. Color: Generally grayish, with a black face marked with a white stripe running from the nose to the eyes. Often has white markings on its crown. Body: Powerful, muscular build with short legs and long claws on the front feet. These claws are essential for digging. Tail: Short and bushy. Head: Broad head with small ears. Distinctive Feature: The striking facial markings are key to identification. The white stripe is almost like a mask.");

            $manager->persist($badger);
            $manager->flush();
        }
    }
}
