<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Loggable\Entity\LogEntry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class LoginAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    private UrlGeneratorInterface $urlGenerator;
    private EntityManagerInterface $defaultEntityManager;


    public function __construct(UrlGeneratorInterface $urlGenerator, EntityManagerInterface $defaultEntityManager )
    {
        $this->urlGenerator = $urlGenerator;
        $this->defaultEntityManager = $defaultEntityManager ;
    }
  

    public function authenticate(Request $request): Passport
    {
        $username = $request->request->get('username', '');

        // var_dump($username); die;
        $request->getSession()->set(Security::LAST_USERNAME, $username);

        return new Passport(
            new UserBadge($username),
            new PasswordCredentials($request->request->get('password', '')),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $nextUrl = NULL;

        $log = new LogEntry();
        $log->setLoggedAt();
        $log->setObjectClass(User::class);
        $log->setUsername($request->request->get('username', ''));
        $log->setVersion(1);
        $log->setData([ 'ip'=> $request->getClientIp() ,'login'=>$request->request->get('username', '') ]);


        $user = $token->getUser();
        
        if ($user != NULL) {
            $role = $token->getUser()->getRoles()[0];
            $log->setObjectId($user->getId());
            if (!$user->getIsActived()) {
                $request->getSession('session')->getFlashBag()->clear();
                $flashBag = $request->getSession('session')->getFlashBag();
                $flashBag->set('warning', 'Inactive account. Please contact your administrator');
                $log->setAction('disabled');
                $nextUrl = $this->urlGenerator->generate('app_login');
            } else {
                $log->setAction('success');
                $nextUrl = $this->urlGenerator->generate('index');
            }
        } else {                 
            $log->setAction('failed');   
            $log->setObjectId(0);   
            $nextUrl = $this->urlGenerator->generate('app_login');
        }
        $this->defaultEntityManager->persist($log);
        $this->defaultEntityManager->flush();
     
        return  new RedirectResponse($nextUrl);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $log = new LogEntry();
        $log->setLoggedAt();
        $log->setObjectClass(User::class);
        $log->setUsername($request->request->get('username', ''));
        $log->setVersion(1);
        $log->setData([ 'ip'=> $request->getClientIp() ,'login'=>$request->request->get('username', '') ]);
        $log->setAction('failed');    
        $this->defaultEntityManager->persist($log);
        $this->defaultEntityManager->flush();
        return  parent::onAuthenticationFailure($request,$exception);
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
    
}
