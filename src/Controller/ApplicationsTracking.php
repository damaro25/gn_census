<?php

namespace App\Controller;

# importation excel

use App\Entity\Districts;
use App\Repository\UserRepository;
use App\Repository\ApplicationsRepository;
use App\Repository\DistrictsRepository;
use App\Repository\LgasRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class ApplicationsTracking extends AbstractController
{
    /**
     * @Route("/candidats/tracking", name="app_applications_tracking", methods={"GET"}, options={"expose"=true})
     * @IsGranted("ROLE_USER")
     */
    public function index(
        Request $request,
        LgasRepository $lgasRepo,
        UserRepository $userRepo,
        DistrictsRepository $repo,
        ApplicationsRepository $appRepo,
        \Doctrine\ORM\EntityManagerInterface $defaultEntityManager = NULL
    ): Response {
        ini_set('memory_limit', '4096M');
        set_time_limit(0);

        $me = $userRepo->findOneBy(['id' => $this->getUser()]);
        // $codLga = $me->getLga()->getCode();
        $districts = $repo->findBy(["lga" => $me->getLga()], ['fdcode' => "ASC"]);


        if ($request->isXmlHttpRequest()) {
            $code = isset($_GET['code']) ? $request->get("code") : "";

            $data = [];
            if ($code == "gambia") {
                $data = $repo->getApplicationsTracking($code);
            } else if (strlen($code) == 1) {
                $data = $this->trackingLgaCandidacies($defaultEntityManager, $code);
            } else if (strlen($code) == 3) {
                $data = $this->trackingDistrictCandidacies($defaultEntityManager, $code);
            }
            // var_dump($data); die;

            return $this->json($data);
        }

        // var_dump(($this->isGranted('ROLE_ADMIN') || $this->isGranted("ROLE_COORDINATION")) ? true : false); die;
        return $this->render('applications/tracking.html.twig', [
            'lgas' => $lgasRepo->findBy([], ['code' => 'ASC']),
            'isAdmin' => ($this->isGranted('ROLE_ADMIN') || $this->isGranted("ROLE_COORDINATION")) ? true : false,
            'isLga' => ($this->isGranted("ROLE_RECRUIT_COM")) ? true : false,
            'districts' => $districts,
            'me' => $me,
            'createAt' => new \DateTime()
        ]);
    }


    /**
     * @Route("/candidats/nbreCandidatPerJour", name="nbreCandidatPerJour", methods={"GET"}, options={"expose"=true})
     * @IsGranted("ROLE_USER")
     */
    public function nbreCandidatPerJour(
        Request $request,
        EntityManagerInterface $em,
        LgasRepository  $lgaRepo,
        DistrictsRepository  $districtsRepo
    ): Response {
        ini_set('memory_limit', '4096M');
        set_time_limit(0);
        $districtCode = (!empty($request->get('districtCode'))) ? $request->get('districtCode') : null;
        $lgaCode =  (!empty($request->get('lgaCode'))) ?  $request->get('lgaCode') : null;



        $sql = "";
        if ($districtCode != null) {
            $district = $districtsRepo->findOneBy(['code' => $districtCode]);
            $sql = "SELECT CONVERT(created_at,DATE) as dateDay  ,count(*) as nbreCandidat from  applications c  where district_id = " . $district->getId() . "  group by ( CONVERT(created_at,DATE))  ORDER  BY CONVERT(created_at,DATE) DESC;";
        } elseif ($lgaCode != null) {
            $lga = $lgaRepo->findOneBy(['code' => $lgaCode]);
            $sql = "SELECT CONVERT(created_at,DATE) as dateDay  ,count(*) as nbreCandidat from  applications c where  lga_id = " . $lga->getId() . "  group by ( CONVERT(created_at,DATE))  ORDER  BY CONVERT(created_at,DATE) DESC ;";
        } else {
            $sql = "SELECT CONVERT(created_at,DATE) as dateDay  ,count(*) as nbreCandidat from  applications c  group by ( CONVERT(created_at,DATE))  ORDER  BY CONVERT(created_at,DATE) DESC ;";
        }


        $data = (array) $em->getConnection()->prepare($sql)->executeQuery()->fetchAllAssociative();
        return  new JsonResponse(
            [

                'data' => $data
            ]
        );
        // return $this->json(['data' => []]);

    }

    /**
     * @Route("/tracking/candidats/api/{code}", name="app_api_tracking_candidats", methods={"GET"}, options={"expose"=true})
     * @IsGranted("ROLE_USER")
     */
    public function myApi(string $code, CommunesArrCommunautesRuralesRepository $repo,  \Doctrine\ORM\EntityManagerInterface $defaultEntityManager)
    {
        $data = [];
        if (strlen($code) == 3 || $code == "gambia") {
            $data = $repo->getApplicationsTracking($code);
        } else {
            $data = $this->trackingDistrictCandidacies($defaultEntityManager, $code);
        }
        return $this->json($data);
    }

    public function trackingLgaCandidacies(\Doctrine\ORM\EntityManagerInterface $defaultEntityManager, $codeLga): array
    {
        $conn = $defaultEntityManager->getConnection();

        $sql = "SELECT SUM(nb_enum_expected) AS nbrExpected,
                        SUM(nbrCandidacies) AS nbrCandidacies,
                        lalga,
                        name,
                        fdcode
                FROM (SELECT COALESCE(nb_enum_expected, 0) AS nb_enum_expected, 
                        (SELECT COUNT(c.id) FROM applications c WHERE c.district_id = district.id) AS nbrCandidacies,
                        (SELECT name FROM lgas WHERE code = $codeLga) AS lalga,
                        district.name AS name,
                        district.fdcode AS fdcode
                FROM districts district
                WHERE district.fdcode LIKE '" . $codeLga . "%') byDistrict
                GROUP BY lalga,name,fdcode
            ";

        $stmt = $conn->prepare($sql);
        $resulats = $stmt->executeQuery()->fetchAllAssociative();

        return (array) $resulats;
    }

    public function trackingDistrictCandidacies(\Doctrine\ORM\EntityManagerInterface $defaultEntityManager, $codeLga): array
    {
        $conn = $defaultEntityManager->getConnection();

        $sql = "SELECT nb_enum_expected AS nbrExpected, 
                       (SELECT COUNT(c.id) FROM applications c WHERE c.district_id = district.id) AS nbrCandidacies,
                       (SELECT 'name' FROM lgas WHERE code = $codeLga) AS lga,
                       'name',
                       fdcode
                FROM districts district
                WHERE district.code LIKE '" . $codeLga . "%'
            ";

        $stmt = $conn->prepare($sql);
        $resulats = $stmt->executeQuery()->fetchAllAssociative();

        return (array) $resulats;
    }
}
