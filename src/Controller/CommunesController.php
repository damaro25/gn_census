<?php

namespace App\Controller;

use App\Entity\Communes;
use App\Repository\CommunesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\PrefecturesRepository;

class CommunesController extends AbstractController
{
   
    /**
     * @Route("/communes", name="app_commune", methods={"GET"}, options={"expose"=true})
     */
    public function index(
        Request $request ,
        CommunesRepository $repo ): Response
    {
        $q = $repo->createQueryBuilder('c')/* ->setMaxResults(30) */->orderBy('c.nom', 'ASC') ; 
        
        if( NULL !=  $request->get('term') ){
            $term = $request->get('term');
            $q=$q->andWhere('c.nom LIKE  :term')
             ->setParameter("term", "%$term%");
        }
        if( NULL !=  $request->get('regionCode') ){
            $regionCode = $request->get('regionCode');
            $q=$q->andWhere('c.code LIKE  :regionCode')
             ->setParameter("regionCode", "$regionCode%");
        }
       
       
        return  new JsonResponse($q->getQuery()->getResult());
    }

    #[Route('/communes-prefecture', name: 'app_communes_prefecture')]
    public function prefByCodeRegion(CommunesRepository $repo,PrefecturesRepository  $repoD, Request $request): Response
    {          
        $pref=$repoD->findOneBy(['code' => $request->get('code')]);
         return  new JsonResponse($repo->findBy(['prefecture' => $pref], ['nom'=>'ASC' ]));
    }

}
