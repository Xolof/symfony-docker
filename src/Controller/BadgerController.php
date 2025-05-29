<?php

namespace App\Controller;

use App\Entity\Badger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BadgerController extends AbstractController
{
    #[Route('/badger/{id}', name: 'badger_show')]
    public function show(EntityManagerInterface $entityManager, int $id): Response
    {
        $badger = $entityManager->getRepository(Badger::class)->find($id);

        if (! $badger) {
            throw $this->createNotFoundException(
                'No badger found for id '.$id
            );
        }

        return $this->render(
            'badger/hello.html.twig', [
                'name' => $badger->getName(),
                'continent' => $badger->getContinent(),
                'description' => $badger->getDescription(),

            ]
        );
    }

    #[Route('/', name: 'app_home')]
    public function showAll(EntityManagerInterface $entityManager): Response
    {
        $badgers = $entityManager
            ->getRepository(Badger::class)
            ->createQueryBuilder('badger')
            ->addOrderBy('badger.id', 'DESC')
            ->getQuery()
            ->execute();

        return $this->render(
            'badger/list.html.twig', [
                'badgers' => $badgers,
            ]
        );
    }

    #[Route('/create/badger', name: 'app_create_badger')]
    public function index(
        EntityManagerInterface $entity_manager,
        ValidatorInterface $validator,
        Request $request
    ): Response {
        $form = $this->createFormBuilder()
            ->add('name', TextType::class)
            ->add('continent', TextType::class)
            ->add('description', TextareaType::class)
            ->add('save', SubmitType::class, ['label' => 'Save Badger'])
            ->getForm();

        $form->handleRequest($request);
        $formData = $form->getData();

        if ($form->isSubmitted()) {
            $badger = new Badger;
            $badger->setName($formData['name']);
            $badger->setContinent($formData['continent']);
            $badger->setDescription($formData['description']);

            $errors = $validator->validate($badger);

            if (count($errors) < 1) {
                $entity_manager->persist($badger);
                $entity_manager->flush();

                $this->addFlash(
                    'success',
                    'Your changes were saved!'
                );

                return $this->redirectToRoute('app_home');
            }
        }

        return $this->render(
            'create_badger/index.html.twig', [
                'message' => 'Create badger',
                'form' => $form,
                'errors' => $errors ?? null,
            ]
        );
    }

    #[Route('/edit/badger/{id}', name: 'badger_edit')]
    public function edit(Request $request, ValidatorInterface $validator, EntityManagerInterface $entityManager, int $id): Response
    {
        $badger = $entityManager->getRepository(Badger::class)->find($id);

        if (! $badger) {
            throw $this->createNotFoundException(
                'No badger found for id '.$id
            );
        }

        $form = $this->createFormBuilder()
            ->add('name', TextType::class, ['data' => $badger->getName()])
            ->add('continent', TextType::class, ['data' => $badger->getContinent()])
            ->add('description', TextareaType::class, ['data' => $badger->getDescription()])
            ->add('save', SubmitType::class, ['label' => 'Save Badger'])
            ->getForm();

        $form->handleRequest($request);
        $formData = $form->getData();

        if ($form->isSubmitted()) {
            $badger->setName($formData['name']);
            $badger->setContinent($formData['continent']);
            $badger->setDescription($formData['description']);

            $errors = $validator->validate($badger);

            if (count($errors) < 1) {
                $entityManager->persist($badger);
                $entityManager->flush();

                $this->addFlash(
                    'success',
                    'Your changes were saved!'
                );

                return $this->redirectToRoute('badger_edit', ['id' => $id]);
            }
        }

        return $this->render(
            'badger/edit.html.twig', [
                'form' => $form,
                'errors' => $errors ?? null,
            ]
        );
    }

    #[Route('/delete/badger/{id}', name: 'badger_delete')]
    public function delete(Request $request, ValidatorInterface $validator, EntityManagerInterface $entityManager, int $id): Response
    {
        $badger = $entityManager->getRepository(Badger::class)->find($id);

        if (! $badger) {
            throw $this->createNotFoundException(
                'No badger found for id '.$id
            );
        }

        $form = $this->createFormBuilder()
            ->add(
                'name', TextType::class, ['data' => $badger->getName(),
                    'attr' => ['disabled' => true]]
            )
            ->add(
                'continent', TextType::class, ['data' => $badger->getContinent(),
                    'attr' => ['disabled' => true]]
            )
            ->add(
                'description', TextareaType::class, ['data' => $badger->getDescription(),
                    'attr' => ['disabled' => true]]
            )
            ->add(
                'save', SubmitType::class,
                ['label' => 'I understand, delete this badger',
                    'attr' => ['class' => 'btn-danger']]
            )
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $errors = $validator->validate($badger);

            if (count($errors) < 1) {
                $entityManager->remove($badger);
                $entityManager->flush();

                $this->addFlash(
                    'success',
                    'Item deleted!'
                );

                return $this->redirectToRoute('app_home');
            }
        }

        return $this->render(
            'badger/delete.html.twig', [
                'form' => $form,
            ]
        );
    }
}
