<?php

namespace App\Controller;

use App\Entity\Admin;
use App\Repository\AdminRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
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
    /**
     * Show a list of all users.
     * Only users with privilege "ROLE_SUPER_ADMIN" can access the admin routes,
     * this is defined in the security config file.
     */
    #[Route('/admin', name: 'admin_home')]
    public function showAll(AdminRepository $adminRepository, Request $request): Response
    {
        $users = $adminRepository->getPaginated();
        $users->setMaxPerPage(4);

        $pageNum = (int) $request->query->get("page", "1");
        $users->setCurrentPage($pageNum);

        return $this->render(
            'admin/list.html.twig',
            ['users' => $users]
        );
    }

    /**
     * Activate a user and send a notification email.
     */
    #[Route('/admin/activate/user/{id}', name: 'admin_activate_user')]
    public function activate(int $id, EntityManagerInterface $entityManager, Request $request, MailerInterface $mailer): Response
    {
        $this->checkCsrf("activate_user", $request);

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

            $email = new Email()
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

    /**
     * Delete a user.
     */
    #[Route('/admin/delete/user/{id}', name: 'admin_delete_user')]
    public function delete(int $id, EntityManagerInterface $entityManager, Request $request): Response
    {
        $this->checkCsrf("delete_user", $request);

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

    /**
     * Get CSRF token and return it as a string.
     */
    protected function getCsrfToken(Request $request): string
    {
        $submittedToken = $request->getPayload()->get('_csrf_token');
        if (!$submittedToken) {
            throw new Exception('Invalid CSRF token.');
        }
        return (string) $submittedToken;
    }

    /**
     * Validate CSRF token and throw Exception if not valid.
     */
    protected function checkCsrf(string $tokenToCheckFor, Request $request): void
    {
        $submittedToken = $this->getCsrfToken($request);

        if (! $this->isCsrfTokenValid($tokenToCheckFor, $submittedToken)) {
            throw new Exception('Invalid CSRF token.');
        }
    }
}
