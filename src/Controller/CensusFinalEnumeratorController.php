<?php

namespace App\Controller;

use App\Entity\User;
use App\Utils\Utils;
use App\Repository\ClassroomRepository;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/enumerators')]
class CensusFinalEnumeratorController extends AbstractController
{

    #[Route('/{slug}/final', name: 'sp_final_enumerators', methods: ['GET'], options: ['expose' => true])]
    #[IsGranted('ROLE_USER')]
    public function index(
        User $sp,
        Request $request,
        ClassroomRepository $repo,
        PaginatorInterface $paginator,
        \WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs $breadcrumbs
    ): Response {

        $breadcrumbs->addRouteItem("Liste superviseurs", "app_users_superviseurs");
        $breadcrumbs->addRouteItem("Enumerators " . $sp->getUsername(), "sp_final_enumerators", ['slug' => $sp->getSlug()]);


        if ($request->isXmlHttpRequest()) {
            $offset = $request->get('start', 0);
            $length = $request->get('length', 20);

            $query = $repo->buildDataTable($request->get('columns'), $request->get('order'), $sp, 1);

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
        return $this->render('team/enumerators.html.twig', ['sp' => $sp]);
    }

    #[Route('/selection', name: 'enumerators_selection', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function selectView(): Response
    {
        return $this->render('team/final-selected.html.twig');
    }

    #[Route('/upload', name: 'app_profilage_upload', methods: ['POST'], options: ['expose' => true])]
    #[IsGranted('ROLE_USER')]
    public function upload(
        KernelInterface $kernel,
        ClassroomRepository $candRepo,
        \Doctrine\ORM\EntityManagerInterface $defaultEntityManager = NULL
    ) {
        ini_set('memory_limit', '4096M');
        set_time_limit(0);

        $filedest = $kernel->getProjectDir() . "/var/cache";

        $name = "";
        $path = "";
        if (!empty($_FILES["file"]["name"])) {
            $name = $_FILES["file"]["name"];
            $path = $_FILES['file']['tmp_name'];

            if (!file_exists($filedest)) {
                mkdir($filedest, 0777, true);
            }

            try {
                move_uploaded_file($path, $filedest . "/" . $name);
            } catch (FileException $e) {
                // dd($e);
            }
            $spreadsheet = IOFactory::load($filedest . "/" . $name);

            // récupère le nom des colonnes
            $mapColumns = Utils::mapExcelColumns($spreadsheet);

            // TODO: verifie l'existence des col d'entêtes 
            $excelColonnes = ['LOGIN_SUPERVISOR', 'IS_PROFILE', 'LOGIN_ENUMERATOR', 'NOTE_FORMATION'];

            $col = Utils::excelNotFoundColumnException($excelColonnes, $mapColumns);

            if (!empty($col)) {
                Utils::deleteExcelTmpFile($name, $filedest . "/");
                return $this->json("Le fichier n'a pas la colonne [" . $col . "]", 500);
            }

            $spIndex = array_search("LOGIN_SUPERVISOR", $mapColumns, true);
            $isProfileIndex = array_search("IS_PROFILE", $mapColumns, true);
            $loginEnumeratorIndex = array_search("LOGIN_ENUMERATOR", $mapColumns, true);
            $noteIndex = array_search("NOTE_FORMATION", $mapColumns, true);

            $row = $spreadsheet->getActiveSheet()->removeRow(1);
            $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

            $defaultEntityManager->beginTransaction();

            try {
                foreach ($sheetData as $Row) {
                    $loginSP = $Row[$spIndex];
                    $isProfile = $Row[$isProfileIndex];
                    $loginEnumerator = $Row[$loginEnumeratorIndex];
                    $note = $Row[$noteIndex];

                    if (!empty($loginSP) && !empty($isProfile) && !empty($loginEnumerator)) {
                        $isEnumerator = $candRepo->getSupervisorEnumerator($loginSP, $loginEnumerator);
                        $dt = new \DateTime();

                        if ($isEnumerator != null) {
                            $isEnumerator
                                ->setIsProfile($isProfile == 'Yes' ? true : false)
                                ->setUpdateAt($dt)
                                ->setNote($note)
                                ->setOpsaisi($this->getUser());

                            $defaultEntityManager->persist($isEnumerator);
                        }
                    }
                }

                $defaultEntityManager->flush();
                $defaultEntityManager->commit();
                Utils::deleteExcelTmpFile($name, $filedest . "/");
            } catch (\Exception $th) {
                $defaultEntityManager->rollback();
                Utils::deleteExcelTmpFile($name, $filedest . "/");
                return $this->json($th->getMessage(), 500);
            }

            return $this->json([], 200);
        }
    }

    #[Route('/{slug}/init-csdb/{login}/{isdel}', name: 'app_start_census', methods: ['GET'], options: ['expose' => true])]
    #[IsGranted('ROLE_USER')]
    public function lancerEpcCollecte(
        User $supervisor,
        \Doctrine\ORM\EntityManagerInterface $defaultEntityManager = NULL,
        ClassroomRepository $classroomRepository,
        KernelInterface $kernel,
        string $login = "",
        $isdel = NULL
    ): JsonResponse {

        ini_set('memory_limit', '4096M');
        set_time_limit(0);

        if ($isdel != NULL) {
            $enumerator = $classroomRepository->findOneBy(['username' => $login]);
            $enumerator
                ->setDeleted($isdel)
                ->setUpdateAt(new \DateTime());

            $defaultEntityManager->persist($enumerator);
            $defaultEntityManager->flush();
        }

        $response = NULL;
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $username = $supervisor->getUsername();
        $loginCmd = NULL;

        if ($login != "") {
            $loginCmd = new ArrayInput([
                'command' => 'csweb:add-dict',
                'username' => $username,
                'enumUserName' => $login,
            ]);
        } else {
            $loginCmd = new ArrayInput([
                'command' => 'csweb:add-dict',
                'username' => $username
            ]);
        }

        $output = new BufferedOutput();
        $content = [];

        try {

            $exitCodeDispatching =   $application->run($loginCmd, $output);
            $content['login'] = ['msg' => $output->fetch(), 'exitCode' => $exitCodeDispatching];

            $response = new JsonResponse($content);
        } catch (\Exception $e) {
            $content = [];
            $content['erreur'] = $e->getMessage();
            $response =  new JsonResponse($content, 500);
        }

        return $response;
    }
}
