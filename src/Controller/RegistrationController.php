<?php

namespace App\Controller;

use App\Entity\Admin;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistrationController extends AbstractController
{
    public function __construct(protected EntityManagerInterface $entityManager) {}

    #[Route('/register', name: 'register')]
    public function index(UserPasswordHasherInterface $passwordHasher, ValidatorInterface $validator, EntityManagerInterface $entity_manager, Request $request): Response
    {
        $form = $this->createFormBuilder()
            ->add('email', TextType::class)
            ->add('password', PasswordType::class)
            ->add('save', SubmitType::class, ['label' => 'Register'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $admin = new Admin;
            $roles = $admin->getRoles();
            // $roles[] = "ROLE_SUPER_ADMIN";

            $formData = $form->getData();
            $email = $formData['email'];

            $plaintextPassword = $formData['password'];

            $admin->validateRawPassword($plaintextPassword);

            $hashedPassword = $passwordHasher->hashPassword(
                $admin,
                $plaintextPassword
            );

            $admin->setPassword($hashedPassword);
            $admin->setRoles($roles);
            $admin->setEmail($email);
            // $admin->setIsActive(false);

            $errors = $validator->validate($admin);

            if (count($errors) < 1) {
                $this->entityManager->persist($admin);
                $this->entityManager->flush();

                $this->addFlash(
                    'success',
                    'User created.'
                );

                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render(
            'register/index.html.twig', [
                'title' => 'Register a new user',
                'form' => $form,
                'errors' => $errors ?? null,
            ]
        );
    }
}
