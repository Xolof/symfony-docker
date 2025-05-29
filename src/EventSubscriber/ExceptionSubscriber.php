<?php

namespace App\EventSubscriber;

use App\Security\Exceptions\NotActivatedException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ExceptionSubscriber implements EventSubscriberInterface
{
    /**
     * Constructor
     *
     * @param UrlGeneratorInterface $urlGenerator For generating url from the name of a route.
     * @param RequestStack          $requestStack For getting the session.
     */
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly RequestStack $requestStack
    ) {
    }

    /**
     * Get Exception events from kernel.
     */
    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::EXCEPTION => 'onKernelException'];
    }

    /**
     * Define custom actions for certain exceptions.
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        // If the user is not activated we redirect to the login route.
        if ($exception instanceof NotActivatedException) {
            // @phpstan-ignore method.notFound
            $this->getSession()->getFlashBag()->add('error', $exception->getMessage());
            $response = new RedirectResponse($this->urlGenerator->generate('app_login'));
            $event->setResponse($response);
        }
    }

    /**
     * Get the Session so that we can get the flashBag from it.
     */
    protected function getSession(): Session|SessionInterface
    {
        return $this->requestStack->getSession();
    }
}
