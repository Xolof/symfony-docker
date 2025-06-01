<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Cookie;

class ThemeController extends AbstractController
{
    /**
     * Toggle dark or light theme.
     */
    #[Route('/theme', name: 'app_theme')]
    public function setTheme(Request $request): RedirectResponse
    {
        $theme = $request->cookies->get('theme') ?? 'dark';
        $updatedTheme = $theme === 'dark' ? 'light' : 'dark';

        $cookie = new Cookie('theme', $updatedTheme);

        $referer = $request->server->get('HTTP_REFERER');

        $response = new RedirectResponse($referer);
        $response->headers->setCookie($cookie);

        return $response;
    }
}
