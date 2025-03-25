<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Presentiel;
use App\Entity\PresentielEnumerator;
use App\Entity\PresentielFiles;
use App\Utils\Utils;
use App\Repository\ClassroomRepository;
use Symfony\Component\Uid\Uuid;
use App\Repository\PresentielRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\PresentielEnumeratorRepository;
use App\Repository\PresentielFilesRepository;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelInterface;
use ZipArchive;

#[Route('/presentiel')]
class CensusPresentielController extends AbstractController
{
    #[Route('/{slug}/sp', name: 'app_sp_presentiels', methods: ['GET'], options: ['expose' => true])]
    #[IsGranted('ROLE_USER')]
    public function index(
        User $sp,
        Request $request,
        PresentielRepository $repo,
        PaginatorInterface $paginator,
    ): Response {

        $offset = $request->get('start', 0);
        $length = $request->get('length', 20);

        $query = $repo->buildDataTable($request->get('columns'), $request->get('order'), $sp);

        $formations = $paginator->paginate(
            $query,
            intval(($offset + 1) / $length) + 1,
            $length
        );

        return  new JsonResponse(
            [
                "draw" => $request->get('draw', 4),
                "recordsTotal" => ($repo->createQueryBuilder('a')->select("COUNT(a.id)")->getQuery()->getSingleScalarResult()),
                'recordsFiltered' => $formations->getTotalItemCount(),
                'data' => $formations->getItems()
            ]
        );
    }

    #[Route('/{slug}/save-training', name: 'app_sp_training_save', methods: ['POST'], options: ['expose' => true])]
    #[IsGranted('ROLE_USER')]
    public function save(
        User $sp,
        Request $request,
        \Doctrine\ORM\EntityManagerInterface $defaultEntityManager = NULL,
        PresentielRepository $repo,
        PresentielEnumeratorRepository $presentielEnumeratorRepository
    ): JsonResponse {
        try {
            $defaultEntityManager->beginTransaction();

            $dt = new \DateTime();

            $guid = Uuid::v6();
            $dateAt = new \DateTime($request->get('_dayAt'));

            $presentiel = $repo->findOneBy(['supervisor' => $sp, 'dayAt' => $dateAt]);

            if ($presentiel == NULL) {

                $presentiel = new Presentiel();
                $presentiel
                    ->setSupervisor($sp)
                    ->setDayAt($dateAt)
                    ->setCreateAt($dt)
                    ->setUpdateAt($dt)
                    ->setUuid($guid->toRfc4122())
                    ->setOpsaisi($this->getUser());

                $defaultEntityManager->persist($presentiel);
                $defaultEntityManager->flush();

                foreach ($sp->getClassrooms() as $enumerator) {

                    $isAdd = $presentielEnumeratorRepository->findOneBy(['presentiel' => $presentiel, "enumeratorClassroom" => $enumerator]);
                    if ($isAdd == NULL) {
                        $prAgent = new PresentielEnumerator();
                        $prAgent
                            ->setPresentiel($presentiel)
                            ->setEnumeratorClassroom($enumerator)
                            ->setIsPresent(false)
                            ->setCreateAt($dt)
                            ->setUpdateAt($dt)
                            ->setOpsaisi($this->getUser());

                        $defaultEntityManager->persist($prAgent);
                    }
                }
            }

            $defaultEntityManager->flush();
            $defaultEntityManager->commit();

            return $this->json("Jour de formation ajouté avec succès.");
        } catch (\Exception $th) {
            $defaultEntityManager->rollback();
            return $this->json($th->getMessage(), 500);
        }
    }

    #[Route('/{slug}/delete-presentiel', name: 'app_sp_presentiel_delete', methods: ['POST'], options: ['expose' => true])]
    #[IsGranted('ROLE_USER')]
    public function delete(
        Presentiel $presentiel,
        \Doctrine\ORM\EntityManagerInterface $defaultEntityManager = NULL,
    ): JsonResponse {
        try {
            $defaultEntityManager->beginTransaction();

            foreach ($presentiel->getPresentielEnumerators() as $pCand) {
                $defaultEntityManager->remove($pCand);
            }

            $defaultEntityManager->remove($presentiel);

            $defaultEntityManager->flush();
            $defaultEntityManager->commit();

            return $this->json([]);
        } catch (\Exception $th) {
            $defaultEntityManager->rollback();
            return $this->json($th->getMessage(), 500);
        }
    }

    #[Route('/sp-presentiel/{slug}', name: 'app_sp_day_presentiel', methods: ['GET'], options: ['expose' => true])]
    #[IsGranted('ROLE_USER')]
    public function presentiels(
        Presentiel $presentiel,
        Request $request,
        PresentielEnumeratorRepository $repo,
        PaginatorInterface $paginator,
        \WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs $breadcrumbs
    ): Response {

        $breadcrumbs->addRouteItem("Liste des formations", "app_sp_team", ['slug' => $presentiel->getSupervisor()->getSlug()]);
        $breadcrumbs->addRouteItem("formation du " . $presentiel->getDayAt()->format('d/m/Y'), "app_sp_day_presentiel", ['slug' => $presentiel->getSlug()]);

        if ($request->isXmlHttpRequest()) {
            $offset = $request->get('start', 0);
            $length = $request->get('length', 20);

            $query = $repo->buildDataTable($request->get('columns'), $request->get('order'), $presentiel);

            $presentiels = $paginator->paginate(
                $query,
                intval(($offset + 1) / $length) + 1,
                $length
            );

            return  new JsonResponse(
                [
                    "draw" => $request->get('draw', 4),
                    "recordsTotal" => ($repo->createQueryBuilder('a')->select("COUNT(a.id)")->getQuery()->getSingleScalarResult()),
                    'recordsFiltered' => $presentiels->getTotalItemCount(),
                    'data' => $presentiels->getItems()
                ]
            );
        }
        return $this->render('team/presentiel.html.twig', ['presentiel' => $presentiel]);
    }

    #[Route('/{id}/{status}/presence', name: 'app_presence_save', methods: ['POST'], options: ['expose' => true])]
    #[IsGranted('ROLE_USER')]
    public function savePresence(
        PresentielEnumerator $presentielEnumerator,
        $status,
        \Doctrine\ORM\EntityManagerInterface $defaultEntityManager = NULL,
    ): JsonResponse {
        try {
            $dt = new \DateTime();

            $presentielEnumerator
                ->setIsPresent($status)
                ->setUpdateAt($dt)
                ->setOpsaisi($this->getUser());

            $defaultEntityManager->persist($presentielEnumerator);
            $defaultEntityManager->flush();

            return $this->json([]);
        } catch (\Exception $th) {
            return $this->json($th->getMessage(), 500);
        }
    }

    #[Route('/{slug}/presentiel-files', name: 'app_presentiel_files', methods: ['GET'], options: ['expose' => true])]
    #[IsGranted('ROLE_USER')]
    public function presentielFiles(
        Presentiel $presentiel,
        Request $request,
        PresentielFilesRepository $repo,
        PaginatorInterface $paginator,
    ): Response {


        $offset = $request->get('start', 0);
        $length = $request->get('length', 20);

        $query = $repo->buildDataTable($request->get('columns'), $request->get('order'), $presentiel);

        $prFiles = $paginator->paginate(
            $query,
            intval(($offset + 1) / $length) + 1,
            $length
        );

        return  new JsonResponse(
            [
                "draw" => $request->get('draw', 4),
                "recordsTotal" => ($repo->createQueryBuilder('a')->select("COUNT(a.id)")->getQuery()->getSingleScalarResult()),
                'recordsFiltered' => $prFiles->getTotalItemCount(),
                'data' => $prFiles->getItems()
            ]
        );
    }

    #[Route('/{slug}/presentiel-file-upload', name: 'app_presentiel_file_upload', methods: ['POST'], options: ['expose' => true])]
    #[IsGranted('ROLE_USER')]
    public function presentielFileUpload(
        Presentiel $presentiel,
        \Doctrine\ORM\EntityManagerInterface $defaultEntityManager = NULL,
    ) {

        $name = "";
        $path = "";
        $cdt = new \DateTime();
        $spIdentify = $presentiel->getSupervisor()->getUsername();
        $dayAt = $presentiel->getDayAt()->format("d_m_Y");
        $basePath = $this->getParameter('presentielBasePath') . "/" . $spIdentify . "_" . $dayAt;

        if (!empty($_FILES["file"]["name"])) {
            $name = $_FILES["file"]["name"];
            $path = $_FILES['file']['tmp_name'];

            $extension = strtolower(substr(strrchr($name, '.'), 1));

            if (!file_exists($basePath)) {
                mkdir($basePath, 0777, true);
            }

            $numFile = str_pad((count($presentiel->getPresentielFiles()) + 1), 2, "0", STR_PAD_LEFT);
            $filePathName = $dayAt . '_' . $numFile . '_' . "$spIdentify.$extension";
            // apply md5 function to generate an unique identifier for the file and concat it with the file extension  
            try {
                move_uploaded_file($path, $basePath . '/' . $filePathName);

                $guid = Uuid::v6();

                $newPrFile = new PresentielFiles();
                $newPrFile
                    ->setPresentiel($presentiel)
                    ->setFileName($filePathName)
                    ->setCreateAt($cdt)
                    ->setUpdateAt($cdt)
                    ->setUuid($guid->toRfc4122())
                    ->setOpsaisi($this->getUser()->getId());
                $defaultEntityManager->persist($newPrFile);
                $defaultEntityManager->flush();

                // set la colonne filename
            } catch (FileException $e) {
                Utils::deleteExcelTmpFile($name, $basePath . "/");
                return $this->json($e->getMessage(), 500);
            }
        }

        return $this->json('Le fichier ' . $filePathName . ' a été enregistré avec succès !', 200);
    }

    #[Route('/{id}/presentiel-file-remove', name: 'app_presentiel_file_remove', methods: ['POST'], options: ['expose' => true])]
    #[IsGranted('ROLE_USER')]
    public function presentielFileRemove(
        PresentielFiles $feuille,
        \Doctrine\ORM\EntityManagerInterface $defaultEntityManager = NULL,
    ) {

        $spIdentify = $feuille->getPresentiel()->getSupervisor()->getUsername();
        $dayAt = $feuille->getPresentiel()->getDayAt()->format("d_m_Y");
        $basePath = $this->getParameter('presentielBasePath') . "/" . $spIdentify . "_" . $dayAt;

        $fileName = $feuille->getFileName();
        try {
            Utils::deleteExcelTmpFile($fileName, $basePath . "/");

            $defaultEntityManager->remove($feuille);
            $defaultEntityManager->flush();

            // set la colonne filename
        } catch (FileException $e) {
            return $this->json($e->getMessage(), 500);
        }

        return $this->json('Le fichier ' . $fileName . ' a été retiré ', 200);
    }

    #[Route('/{slug}/presentiel-file-download', name: 'app_presentiel_file_download', methods: ['GET'], options: ['expose' => true])]
    #[IsGranted('ROLE_USER')]
    public function downloadPresenceFile(PresentielFiles $pFile, Request $request)
    {
        $spIdentify = $pFile->getPresentiel()->getSupervisor()->getUsername();
        $dayAt = $pFile->getPresentiel()->getDayAt()->format("d_m_Y");
        $basePath = $this->getParameter('presentielBasePath') . "/" . $spIdentify . "_" . $dayAt;

        $presenceFile = $pFile->getFilename();

        $path = $basePath . "/" . $presenceFile;

        return $this->file($path, $presenceFile, ResponseHeaderBag::DISPOSITION_INLINE);
    }

    #[Route('/{slug}/presentiel-files-zip', name: 'app_presentiel_zip', options: ['expose' => true])]
    #[IsGranted('ROLE_USER')]
    public function getTicketScreens(Presentiel $presentiel, KernelInterface $kernel)
    {
        if ($presentiel ==  null) {
            return new NotFoundHttpException("aucun présentiel correspondante à cet URI");
        }

        $spIdentify = $presentiel->getSupervisor()->getUsername();
        $dayAt = $presentiel->getDayAt()->format("d_m_Y");
        $custDest = $spIdentify . "_" . $dayAt;
        $basePath = $this->getParameter('presentielBasePath') . "/" . $custDest;


        $destZip = sys_get_temp_dir() . "/$custDest";

        $filesystem = new Filesystem();

        // supprime le dossier créer
        try {
            if (!file_exists(dirname($destZip))) {
                $filesystem = new Filesystem();
                $filesystem->mkdir($destZip, 0777);
            } else {
                $filesystem->remove([$destZip]);

                $filesystem = new Filesystem();
                $filesystem->mkdir($destZip, 0777);
            }
        } catch (\Exception $th) {
            //throw $th;
        }

        try {
            $zip = new \ZipArchive();
            $zip->open($destZip . '.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);

            $names = array_map(function ($file) {
                return $file->getFileName();
            }, $presentiel->getPresentielFiles()->toArray());


            foreach ($names as $currentZipFile) {
                $theFile = $basePath . "/" . $currentZipFile;
                if ($filesystem->exists($theFile)) {
                    $zip->addFile($theFile, $currentZipFile);
                }
            }

            $zip->close();
        } catch (\Throwable $th) {
            throw $th;
        }

        try {
            $filesystem->remove(['symlink', $destZip]);
        } catch (\Throwable $th) {
            //throw $th;
        }

        $response =  new BinaryFileResponse($destZip . ".zip");
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            basename($destZip . ".zip")
        );

        return $response;
    }

}
