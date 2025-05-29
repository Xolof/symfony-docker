<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * SecurityController
 *
 * Handles authentication-related actions such as login and logout.
 */
class SecurityController extends AbstractController
{
    /**
     * Handles user login by rendering the login page
     * and processing authentication errors.
     */
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Get the login error if there is one.
        $error = $authenticationUtils->getLastAuthenticationError();

        // Last username entered by the user.
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render(
            'security/login.html.twig',
            [
                'last_username' => $lastUsername,
                'error' => $error,
            ]
        );
    }

    /**
     * Handles user logout.
     *
     * This method is intentionally blank as the logout process is intercepted
     * by the firewall configuration.
     *
     * @throws \LogicException When the method is called directly
     */
    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException(
            <<<'EOD'
                This method can be blank 
                - it will be intercepted by the logout key on your firewall.
            EOD
        );
    }
}
