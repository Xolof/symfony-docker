<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Cookie;

class CookieInfoController extends AbstractController
{
    /**
     * Set a cookie for remembering if the user has been informed about cookies
     * so that the cookie info banner does not have to be displayed again until
     * the cookie has expired.
     */
    #[Route('/confirm-cookie-info', name: 'confirm_cookie_info')]
    public function confirmCookieInfo(Request $request): RedirectResponse
    {
        $cookie = new Cookie('informedAboutCookies', '1', strtotime('now + 5 months'));

        $referer = $request->server->get('HTTP_REFERER');
        $response = new RedirectResponse($referer);
        $response->headers->setCookie($cookie);

        return $response;
    }
}
