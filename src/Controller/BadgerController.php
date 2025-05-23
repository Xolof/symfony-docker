<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Badger;
use Doctrine\ORM\EntityManagerInterface;

class BadgerController extends AbstractController
{
    #[Route('/badger/{id}', name: 'badger_show')]
    public function show(EntityManagerInterface $entityManager, int $id): Response
    {
        $badger = $entityManager->getRepository(Badger::class)->find($id);

        if (!$badger) {
            throw $this->createNotFoundException(
                'No badger found for id ' . $id
            );
        }

        return $this->render("badger/hello.html.twig", [
            "name" => $badger->getName(),
            "continent" => $badger->getContinent(),
            "description" => $badger->getDescription()

        ]);
    }

    #[Route('/badgers', name: 'badger_show_all')]
    public function showAll(EntityManagerInterface $entityManager): Response
    {
        $badgers = $entityManager->getRepository(Badger::class)->findAll();

        if (!$badgers) {
            throw $this->createNotFoundException(
                'No badgers found'
            );
        }

        // dd($badgers);

        return $this->render("badger/list.html.twig", [
            "badgers" => $badgers
        ]);
    }
}
