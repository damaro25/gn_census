<?php

namespace App\Events;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;

class JwtCreatedSubscriber
{

    public function updateJwtData(JWTCreatedEvent $event)
    {
        $user = $event->getUser();

        $data = $event->getData();
        $data['id'] = $user->getId();
        $data['username'] = $user->getEmail();
        $data['prenom'] = $user->getName();
        $data['nom'] = $user->getSurname();
        $data['isActived'] = $user->getIsActived();
        $data['roles'] = $user->getRoles();

        $event->setData($data);
    }
}
