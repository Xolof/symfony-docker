<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Badger;
use Doctrine\ORM\EntityManagerInterface;

final class CreateBadgerController extends AbstractController
{
    #[Route('/create/badger', name: 'app_create_badger')]
    public function index(EntityManagerInterface $entity_manager): Response
    {


        $description = <<<EOD
            The European badger, also known as the Eurasian badger, 
            is a mustelid native to Europe and parts of Asia. 
            It is classified as least concern on the IUCN Red List due to its wide range and stable population size,
            which is thought to be increasing in some regions.
            The badger is a social, omnivorous mammal that resides in woodlands, pastures, suburbs, and urban parks. It is known for its black and white striped face and a body that is grayish with black and white fur.
        EOD;

        $badger = new Badger();
        $badger->setName("European badger");
        $badger->setContinent("Europe");
        $badger->setDescription($description);

        $entity_manager->persist($badger);
        
        $entity_manager->flush();
        
        return $this->render('create_badger/index.html.twig', [
            'message' => "Badger added!",
        ]);
    }
}
