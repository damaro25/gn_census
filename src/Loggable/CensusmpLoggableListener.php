<?php

declare(strict_types=1);

namespace App\Loggable;

use Gedmo\Loggable\LoggableListener;
use Gedmo\Loggable\Mapping\Event\LoggableAdapter;
use Symfony\Component\Security\Core\Security;

class CensusmpLoggableListener extends LoggableListener
{
    public function __construct(Security $security){
       
        parent::__construct();
        $this->security = $security;
    }

     /**
     * Create a new Log instance
     *
     * @param string $action
     * @param object $object
     *
     * @return \Gedmo\Loggable\Entity\MappedSuperclass\AbstractLogEntry|null
     */
    public  function createLogEntry($action, $object, LoggableAdapter $ea)
    {
        $this->setUsername($this->security->getUser()== NULL ? 'GUEST': $this->security->getUser());
       return  parent::createLogEntry($action ,$object,$ea);
       
    }
    
}
