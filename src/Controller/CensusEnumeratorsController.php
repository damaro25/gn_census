<?php

namespace App\Controller;

use App\Repository\ApplicationsRepository;
use App\Repository\ClassroomRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/candidats')]
class CensusEnumeratorsController extends AbstractController
{
    #[Route('', name: 'app_candidats_home', methods: ['GET'], options: ['expose' => true])]
    #[IsGranted('ROLE_USER')]
    public function index(
        Request $request,
        ApplicationsRepository $repo,
        ClassroomRepository $classroomRepository,
        PaginatorInterface $paginator,
    ): Response {

        if ($request->isXmlHttpRequest()) {
            $offset = $request->get('start', 0);
            $length = $request->get('length', 20);

            $query = $repo->dtCandidats($request->get('columns'), $request->get('order'));

            $candidats = $paginator->paginate(
                $query,
                intval(($offset + 1) / $length) + 1,
                $length
            );

            return  new JsonResponse(
                [
                    "draw" => $request->get('draw', 4),
                    "recordsTotal" => ($repo->createQueryBuilder('a')->select("COUNT(a.id)")->getQuery()->getSingleScalarResult()),
                    'recordsFiltered' => $candidats->getTotalItemCount(),
                    'data' => $candidats->getItems()
                ]
            );
        }

        $totalCandidat = $repo->cptPostulants();
        $totalInClassRoom = $classroomRepository->cptInClassroom();
        $totalProfile = $classroomRepository->cptProfiled();

        return $this->render('applications/candidats.html.twig', [
            'totalCandidat' => $totalCandidat,
            'totalInClassRoom' => $totalInClassRoom,
            'totalProfile' => $totalProfile,
        ]);
    }    
}
