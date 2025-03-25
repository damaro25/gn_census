<?php

namespace App\Events;

use App\Entity\User as AppUser;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\LockedException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccountExpiredException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof AppUser) {
            return;
        }

        if (!$user->getIsActived()) {
            throw new CustomUserMessageAccountStatusException('Inactive account, contact your administrator');
        }

        $today = new \DateTime();

        if ($user->getStartAt() != null && $user->getStartAt()->getTimestamp() > $today->getTimestamp()) {
            throw new CustomUserMessageAccountStatusException("You are only allowed to log in from " . $user->getStartAt()->format("d/m/Y"));
        }
        if ($user->getEndAt() != null && $today->getTimestamp() >= $user->getEndAt()->getTimestamp()) {
            throw new CustomUserMessageAccountStatusException('Account expired, contact your administrator');
        }
    }


    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user instanceof AppUser) {
            return;
        }
    }
}
