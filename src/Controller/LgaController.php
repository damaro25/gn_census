<?php

namespace App\Controller;

use App\Repository\LgasRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LgaController extends AbstractController
{
    /**
     * @Route("/lgas", name="app_lgas", methods={"GET"}, options={"expose"=true})
     */
    public function index(LgasRepository $repo ): Response
    {
        return  new JsonResponse($repo->findBy([], ['name'=>'ASC' ]));
    }

    #[Route('/demo', name: 'app_demo')]
    public function demo(LgasRepository $repo ): Response
    {
        return $this->render('demo.html.twig');
    }


    /**
     * Permet d'afficher la liste des régions du pays
     * 
     * @Route("/censusmp/lgas/{code_lga}", name="censusmp_lgas",methods={"GET"},options={"expose"=true})
     */
    public function liste_lga(
        $code_lga ,
        LgasRepository $repo,
        PaginatorInterface $paginator,
        Request $request
    ): Response {
        // var_dump($code_lga); die();

        if($request->isXmlHttpRequest()){
            $query = $repo->createQueryBuilder('a')
            ->select('a.id,a.nom,a.code');
            if($code_lga != NULL){
            $query->where('a.code = :codeLga')
                ->setParameter('codeLga' , $code_lga);
            }
            return new JsonResponse($query->getQuery()->getResult()) ;
        }
        $query = $repo->createQueryBuilder('a')
            ->getQuery();
        $lgas = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            20

        );


        return $this->render('lga/index.html.twig', [
            'lgas' => $lgas
        ]);
    }
}
