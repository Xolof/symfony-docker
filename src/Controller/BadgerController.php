<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BadgerController extends AbstractController
{
    #[Route('/badger/hello')]
    public function hello(): Response
    {
        $names = [
            "Kjell",
            "Mumin",
            "Kalle Stropp",
            "Grodan Boll"
        ];

        $name = $names[rand(0, count($names) -1)];
        return $this->render("badger/hello.html.twig", [
            "name" => $name
        ]);
    }
}
