<?php

namespace App\Controller;

use App\Entity\Comunes;
use App\Entity\Prefectures;
use App\Repository\ComunesRepository;
use App\Repository\PrefecturesRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PrefecturesController extends AbstractController
{

    /**
     * @Route("/prefectures", name="app_prefectures", methods={"GET"}, options={"expose"=true})
     */
    public function index(
        PrefecturesRepository $repo,
        Request $request
    ): Response {
        $q = $repo->createQueryBuilder('c')/* ->setMaxResults(30) */->orderBy('c.nom', 'ASC');

        if (NULL !=  $request->get('term')) {
            $term = $request->get('term');
            $q = $q->andWhere('c.nom LIKE  :term')
                ->setParameter("term", "%$term%");
        }
        if (NULL !=  $request->get('region')) {
            $regionId = $request->get('region');
            $q = $q->leftJoin('c.region', 'r')
                ->andWhere('r.id = :regionId')
                ->setParameter("regionId", $regionId);
        }
        
        return  new JsonResponse($q->getQuery()->getResult());
    }

    #[Route('/prefectures-region', name: 'app_prefectures_region')]
    public function prefByCodeRegion(PrefecturesRepository $repo, Request $request): Response
    {
        return  new JsonResponse($repo->findBy(['codeParent' => $request->get('code')], ['nom' => 'ASC']));
    }


    /**
     * Permet d'afficher la liste des prefecturess du pays
     *
     * @Route("/pigor/prefectures", name="pigor_prefectures")
     * 
     * @param PrefecturesRepository $repo
     * @param Request $request
     * @return Response
     */
    public function Liste_pref(
        PrefecturesRepository $repo,
        Request $request,
        PaginatorInterface $paginator,
        SerializerInterface $serializer
    ): Response {
        //$repo->findBy([], ['region' => 'ASC'];
        $query = $repo->createQueryBuilder('a')
            ->orderBy('a.region', 'ASC')
            ->getQuery();
        $prefectures = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            20

        );
        if ($request->isXmlHttpRequest()) {
            $prefs  = $query->getResult();
            $data =  $serializer->serialize(['data' => $prefs], JsonEncoder::FORMAT, ['groups' => 'localite']);
            return new JsonResponse($data ==  NULL ? NULL : $data, Response::HTTP_OK, ['Content-Type' => 'application/json'], TRUE);
        }

        return $this->render('prefecture/index.html.twig', [
            'prefectures' => $prefectures
        ]);
    }

    /**
     * @Route("/prefs_arrnd", name="app_prefs_arrnd_cacrs")
     * @IsGranted("ROLE_USER")
     */
    public function prefecturesView(Request $request, PrefecturesRepository $repo, PaginatorInterface $paginator, SerializerInterface $serializer): Response
    {
        if ($request->isXmlHttpRequest()) {

            $query = $repo->createQueryBuilder('a')
                ->orderBy('a.region', 'ASC')
                ->getQuery();

            $prefs  = $query->getResult();
            $data =  $serializer->serialize([
                'data' => $prefs,
                'recordsTotal' => 46,
                'recordsFiltered' => 50,
            ], JsonEncoder::FORMAT, ['groups' => 'localite']);
            return new JsonResponse($data ==  NULL ? NULL : $data, Response::HTTP_OK, ['Content-Type' => 'application/json'], TRUE);
        }

        return $this->render('params/pref_arr_cacr.html.twig');
    }

}
