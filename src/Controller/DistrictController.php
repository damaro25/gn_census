<?php

namespace App\Controller;

use App\Repository\DistrictsRepository;
use App\Repository\LgasRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DistrictController extends AbstractController
{
    /**
     * @Route("/districts", name="app_districts", methods={"GET"}, options={"expose"=true})
     */
    public function index(DistrictsRepository $repo ): Response
    {
        return  new JsonResponse($repo->findBy([], ['name'=>'ASC' ]));
    }

    #[Route('/demo', name: 'app_demo')]
    public function demo(DistrictsRepository $repo ): Response
    {
        return $this->render('demo.html.twig');
    }


    /**
     * @Route("/districtsInLga", name="app_districts_in_region", methods={"GET"}, options={"expose"=true})
     */
    public function listPerLga(
        DistrictsRepository $repo,
        Request $request
    ): Response {
        $q = $repo->createQueryBuilder('d')/* ->setMaxResults(30) */->orderBy('d.name', 'ASC');

        if (NULL !=  $request->get('term')) {
            $term = $request->get('term');
            $q = $q->andWhere('d.name LIKE  :term')
                ->setParameter("term", "%$term%");
        }
        if (NULL !=  $request->get('lga')) {
            $lgaId = $request->get('lga');
            $q = $q->leftJoin('d.lga', 'l')
                ->andWhere('l.id = :lgaId')
                ->setParameter("lgaId", $lgaId);
        }
        
        return  new JsonResponse($q->getQuery()->getResult());
    }


    /**
     * @Route("/districts-lga", name="app_districts_lga", methods={"GET"})
    */
    public function deptByCodeRegion(DistrictsRepository $repo, LgasRepository $lRepo, Request $request): Response
    {
        $Lga = $lRepo->findOneBy(['code' => $request->get('code')]);
        return  new JsonResponse($repo->findBy(['lga' => $Lga], ['name' => 'ASC']));
    }

    /**
     * Permet d'afficher la liste des régions du pays
     * 
     * @Route("/censusmp/districts/{code_district}", name="censusmp_districts",methods={"GET"},options={"expose"=true})
     */
    public function liste_district(
        $code_district ,
        DistrictsRepository $repo,
        PaginatorInterface $paginator,
        Request $request
    ): Response {
        // var_dump($code_district); die();

        if($request->isXmlHttpRequest()){
            $query = $repo->createQueryBuilder('a')
            ->select('a.id,a.nom,a.code');
            if($code_district != NULL){
            $query->where('a.code = :codeDistrict')
                ->setParameter('codeDistrict' , $code_district);
            }
            return new JsonResponse($query->getQuery()->getResult()) ;
        }
        $query = $repo->createQueryBuilder('a')
            ->getQuery();
        $districts = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            20

        );


        return $this->render('district/index.html.twig', [
            'districts' => $districts
        ]);
    }
}
