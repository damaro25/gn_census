<?php

namespace App\Security\Voter;

use App\Entity\User ;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Psr\Log\LoggerInterface;

class SuperAdminVoter extends Voter
{
    const SUPER_ADMIN_ROLE = "ROLE_SUPER_ADMIN";
    private $logger;

    public function __construct(Security $security, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->security = $security;
    }

    protected function supports($attribute, $subject):bool
    {

        // if the attribute isn't one we support, return false
        if ($this->security->getUser() == null || !in_array(self::SUPER_ADMIN_ROLE, $this->security->getUser()->getRoles())) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token):bool
    {
        $user = $token->getUser();
        $this->logger->debug('user voter voteOnAttribute: '.$user->getUserIdentifier());

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }
        return $this->security->isGranted(self::SUPER_ADMIN_ROLE);
        // id has user 

        // throw new \LogicException('This code should not be reached!');
    }
}
