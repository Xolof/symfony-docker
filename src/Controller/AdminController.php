<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Admin;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

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
            "admin/list.html.twig", [
            "users" => $users
            ]
        );
    }


    #[Route('/admin/activate/user/{id}', name: 'admin_activate_user')]
    public function activate(int $id, EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager->getRepository(Admin::class)
            ->find($id);

        if (!$user->isActive()) {
            $user->setIsActive(true);
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash(
                'success',
                'User activated!'
            );
            return $this->redirectToRoute('admin_home');
        };

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

        if (!$user) {
            throw new NotFoundHttpException("User with id $id not found.");   
        }

        if (in_array("ROLE_SUPER_ADMIN", $user->getRoles())) {
            throw new AccessDeniedHttpException("Operation not allowed.");
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
