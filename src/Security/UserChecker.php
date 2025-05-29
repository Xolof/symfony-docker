<?php

namespace App\Security;

use App\Security\Exceptions\NotActivatedException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    /**
     * Uses the preAuth hook to check if the account has been activated.
     * If it is not activated an exception is thrown.
     */
    public function checkPreAuth(UserInterface $user): void
    {
        if (! $user->isActive()) {
            throw new NotActivatedException('Your account has not yet been activated.');
        }
    }

    /**
     * This function needs to be here to comply with the UserCheckerInterface
     */
    public function checkPostAuth(UserInterface $user): void
    {
    }
}
