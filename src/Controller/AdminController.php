<?php

namespace App\Controller;

use App\Entity\Admin;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin_home')]
    public function showAll(EntityManagerInterface $entityManager): Response
    {
        $users = $entityManager
            ->getRepository(Admin::class)
            ->createQueryBuilder('u')
            ->addOrderBy('u.id', 'DESC')
            ->getQuery()
            ->execute();

        return $this->render(
            'admin/list.html.twig', [
                'users' => $users,
            ]
        );
    }

    #[Route('/admin/activate/user/{id}', name: 'admin_activate_user')]
    public function activate(int $id, EntityManagerInterface $entityManager, Request $request, MailerInterface $mailer): Response
    {
        $user = $entityManager->getRepository(Admin::class)
            ->find($id);

        if (! $user->isActive()) {
            $user->setIsActive(true);
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash(
                'success',
                'User activated!'
            );

            $userEmail = $user->getEmail();
            $host = $request->getHost();

            $email = (new Email)
                ->from("admin@$host")
                ->to($userEmail)
                ->subject('Your account has been activated')
                ->html("<h1>Your account has been activated</h1><p>Go to <a href='$host/login'>$host/login</a> to login.</p>");

            $mailer->send($email);

            return $this->redirectToRoute('admin_home');
        }

        $this->addFlash(
            'info',
            'That user is already activated.'
        );

        return $this->redirectToRoute('admin_home');
    }

    #[Route('/admin/delete/user/{id}', name: 'admin_delete_user')]
    public function delete(int $id, EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager->getRepository(Admin::class)
            ->find($id);

        if (! $user) {
            throw new NotFoundHttpException("User with id $id not found.");
        }

        if (in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            throw new AccessDeniedHttpException('Operation not allowed.');
        }

        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash(
            'success',
            'User deleted!'
        );

        return $this->redirectToRoute('admin_home');
    }
}
