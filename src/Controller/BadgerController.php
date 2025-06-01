<?php

namespace App\Controller;

use App\Entity\Badger;
use App\Form\BadgerForm;
use App\Repository\BadgerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Helpers\Markdowner;
use App\Service\FileUploader;
use Psr\Log\LoggerInterface;
use Exception;
use Symfony\Component\Form\FormInterface;

class BadgerController extends AbstractController
{
    /**
     * Show a list of all badgers.
     */
    #[Route('/', name: 'app_home')]
    public function showAll(
        BadgerRepository $badgerRepository,
        Request $request
    ): Response {

        $search = $request->query->get("s");

        $badgers = $badgerRepository->getPaginated($search);
        $badgers->setMaxPerPage(4);
        $badgers->setCurrentPage($request->query->get("page", 1));

        $markdowner = new Markdowner();

        foreach ($badgers as $badger) {
            $badger->setDescription($markdowner->print($badger->getDescription()));
        }

        return $this->render(
            'badger/list.html.twig',
            [
                'badgers' => $badgers,
                'search' => $search
            ]
        );
    }

    /**
     * Show a specific badger.
     */
    #[Route('/badger/{id}', name: 'badger_show')]
    public function show(EntityManagerInterface $entityManager, int $id): Response
    {
        $badger = $entityManager->getRepository(Badger::class)->find($id);

        if (! $badger) {
            throw $this->createNotFoundException(
                'No badger found for id ' . $id
            );
        }

        $markdowner = new Markdowner();
        $description = $markdowner->print($badger->getDescription());

        return $this->render(
            'badger/hello.html.twig',
            [
                'name' => $badger->getName(),
                'continent' => $badger->getContinent(),
                'description' => $description,
                "imageFilename" => $badger->getImageFilename()
            ]
        );
    }

    /**
     * Create a badger.
     */
    #[Route('/create/badger', name: 'app_create_badger')]
    public function index(
        EntityManagerInterface $entityManager,
        Request $request,
        FileUploader $fileUploader,
        LoggerInterface $logger
    ): Response {
        $badger = new Badger();

        $form = $this->createForm(BadgerForm::class, $badger, ['image_is_required' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $errors = $this->processBadger($badger, $form, $fileUploader, $logger);
            if (count($errors) < 1) {
                $entityManager->persist($badger);
                $entityManager->flush();
                $this->addFlash(
                    'success',
                    'Your changes were saved!'
                );
                return $this->redirectToRoute('app_home');
            }
        }

        return $this->render(
            'create_badger/index.html.twig',
            [
                'form' => $form,
                'errors' => $errors ?? null
            ]
        );
    }

    /**
     * Edit a badger.
     */
    #[Route('/edit/badger/{id}', name: 'badger_edit')]
    public function edit(
        Request $request,
        EntityManagerInterface $entityManager,
        int $id,
        FileUploader $fileUploader,
        LoggerInterface $logger
    ): Response {
        $badger = $entityManager->getRepository(Badger::class)->find($id);

        if (! $badger) {
            throw $this->createNotFoundException(
                'No badger found for id ' . $id
            );
        }

        $form = $this->createForm(BadgerForm::class, $badger, ['image_is_required' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $errors = $this->processBadger($badger, $form, $fileUploader, $logger);
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
            'badger/edit.html.twig',
            [
                'form' => $form,
                'errors' => $errors ?? null,
                'currentImageFileName' => $badger->getImageFileName(),
                'id' => $id
            ]
        );
    }

    /**
     * Process creating or updating a badger.
     *
     * @return array<string, string> An associative array with errors.
     */
    protected function processBadger(
        object $badger,
        FormInterface $form,
        FileUploader $fileUploader,
        LoggerInterface $logger
    ): array {
        $badger->setName($form->get('name')->getData());
        $badger->setContinent($form->get('continent')->getData());
        $badger->setDescription($form->get('description')->getData());
        $imageFile = $form->get('image')->getData();
        if ($imageFile) {
            if ($imageFile->isValid()) {
                try {
                    $uploadResult = $fileUploader->upload($imageFile);
                    $badger->setImageFilename($uploadResult);
                } catch (Exception $e) {
                    $logger->error($e);
                    $failedUploadViolation = ["message" => "File upload failed."];
                }
            } else {
                $logger->error($imageFile->getErrorMessage());
                $invalidFileMessage = ["message" => "The image file is invalid."];
            }
        }

        $formIteratorErrors = $form->getErrors(true);

        $otherErrors = [];
        if (isset($invalidFileMessage)) {
            $otherErrors[] = $invalidFileMessage;
        }
        if (isset($failedUploadViolation)) {
            $otherErrors[] = $failedUploadViolation;
        }

        $result = array_merge([$otherErrors, $formIteratorErrors])[0];
        return $result;
    }

    /**
     * Delete a badger.
     */
    #[Route('/delete/badger/{id}', name: 'badger_delete')]
    public function delete(Request $request, ValidatorInterface $validator, EntityManagerInterface $entityManager, int $id): Response
    {
        $badger = $entityManager->getRepository(Badger::class)->find($id);

        if (! $badger) {
            throw $this->createNotFoundException(
                'No badger found for id ' . $id
            );
        }

        $form = $this->createFormBuilder()
            ->add(
                'name',
                TextType::class,
                [
                    'data' => $badger->getName(),
                    'attr' => ['disabled' => true],
                ]
            )
            ->add(
                'continent',
                TextType::class,
                [
                    'data' => $badger->getContinent(),
                    'attr' => ['disabled' => true],
                ]
            )
            ->add(
                'description',
                TextareaType::class,
                [
                    'data' => $badger->getDescription(),
                    'attr' => ['disabled' => true],
                ]
            )
            ->add(
                'save',
                SubmitType::class,
                [
                    'label' => 'I understand, delete this badger',
                    'attr' => ['class' => 'btn-danger'],
                ]
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

        $imageFileName = $badger->getImageFileName();

        return $this->render(
            'badger/delete.html.twig',
            [
                'form' => $form,
                'imageFileName' => $imageFileName,
                'id' => $id
            ]
        );
    }
}
