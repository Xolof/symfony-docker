<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Badger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class CreateBadgerController extends AbstractController
{
    #[Route('/create/badger', name: 'app_create_badger')]
    public function index(
        EntityManagerInterface $entity_manager,
        ValidatorInterface $validator,
        Request $request
    ): Response
    {



        // dd($errors);

        $form = $this->createFormBuilder()
            ->add('name', TextType::class)
            ->add('continent', TextType::class)
            ->add('description', TextType::class)
            ->add('save', SubmitType::class, ['label' => 'Save Badger'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $badger = new Badger();

            $errors = $validator->validate($badger);

            $formData = $form->getData();
            // dd($formData);
            $badger->setName($formData["name"]);
            $badger->setContinent($formData["continent"]);
            $badger->setDescription($formData["description"]);
            $entity_manager->persist($badger);
            $entity_manager->flush();

            return $this->redirectToRoute('badger_show_all');
        }
        
        return $this->render('create_badger/index.html.twig', [
            'message' => "Create badger",
            'form' => $form,
            'errors' => $errors ?? null
        ]);
    }
}
