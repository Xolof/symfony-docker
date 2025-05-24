<?php

namespace App\Security;

use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Security\Exceptions\NotActivatedException;

class UserChecker implements UserCheckerInterface
{

    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user->isActive()) {
            throw new NotActivatedException("Your account has not yet been activated.");
        };
    }

    public function checkPostAuth(UserInterface $user): void
    {

    }
}
