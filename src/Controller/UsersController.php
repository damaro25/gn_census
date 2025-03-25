<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\TiketsRepository;
use App\Repository\RegionsRepository;
use App\Repository\ApplicationsRepository;
use App\Repository\LgasRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
// cmd
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



class UsersController extends AbstractController
{

    private $defaultEntityManager;
    private $userRepo;
    private $ticketRepo;

    public function __construct(
        \Doctrine\ORM\EntityManagerInterface $defaultEntityManager = NULL,
        UserRepository $userRepo
    ) {
        $this->defaultEntityManager = $defaultEntityManager;
        $this->userRepo = $userRepo;
    }


    /**
     * @Route("/", name="index")
     * @IsGranted("ROLE_USER")
    */
    public function index(Request $request): Response
    {

        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('dynamic-dashboard');
        } else if ($this->isGranted('ROLE_RECRUIT_COM')) {
            return $this->redirectToRoute('app_candidats_lga_recruit', ['id' => $this->getUser()->getLga()->getId()]);
        }else if ($this->isGranted('ROLE_MINISTRE')) {
            return $this->redirectToRoute('dynamic-dashboard');
        } else {
            return $this->redirectToRoute('index_no_permission');
        }
    }



    /**
     * @Route("/users/no-permission", name="index_no_permission")
    */
    public function noPermission(): Response
    {
        return $this->render('users/no_permission.html.twig');
    }
   

    /**
     * @Route("/candidat/cni_check", name="app_candidats_nin_checks")
    */
    public function checksCandidatCNI(ApplicationsRepository $candidatRepo, UserRepository $userRepo, Request $request): Response
    {
        $candidats = $candidatRepo->findBy(['nin' => $request->get('nin')]);
        $user = $userRepo->findOneBy(['nin' => $request->get('nin')]);

        $isCNI = (count($candidats) > 1 ||  $user != null) ? TRUE : FALSE;
        return  new JsonResponse($isCNI, 200);
    }
}
