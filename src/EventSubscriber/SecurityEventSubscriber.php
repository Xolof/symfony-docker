<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;

/**
 * Subscribe to login and logout events and add flash messages.
 */
class SecurityEventSubscriber implements EventSubscriberInterface
{
    /**
     * Constructor
     *
     * @param RequestStack $requestStack Used for adding flashmessage.
     */
    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    /**
     * Define the events to subscribe to.
     */
    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
            LogoutEvent::class => 'onLogout',
        ];
    }

    /**
     * Set a flashmessage upon successful login.
     */
    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $session = $this->requestStack->getSession();
        $session->getFlashBag()->add('success', 'You have successfully logged in!');
    }

    /**
     * Set a flashmessage upon successful loout.
     */
    public function onLogout(LogoutEvent $event): void
    {
        $session = $this->requestStack->getSession();
        $session->getFlashBag()->add('success', 'You have been logged out successfully.');
    }
}
