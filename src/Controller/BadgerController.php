<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BadgerController
{
    #[Route('/badger/number')]
    public function number(): Response
    {
        $number = random_int(0, 100);

        return new Response(
            '<html>
            <body>
            <img src="/img/badger.jpg" alt="badger" height=250px />
            <p>Badger number: '.$number.'</p>
            </body>
            </html>'
        );
    }
}