<?php

namespace App\Controller;

use DateTime;
use Exception;
use App\Entity\User;
use App\PDF\CensusmpPdf;
use App\Utils\Utils;
use App\Entity\Applications;
use App\Entity\Districts;
use App\Entity\Lgas;
use Psr\Log\LoggerInterface;
use App\Repository\UserRepository;
use Symfony\Component\Finder\Finder;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Repository\ApplicationsRepository;
use App\Repository\DistrictsRepository;
use App\Repository\LgasRepository;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
// use UltraMsg\WhatsAppApi;
//use \Httpful\Request as Orange;

class ApplicationsController extends AbstractController
{
    public static $ALLOWED_EXTENSION = [
        "JPG", "JPEG", "PNG", "GIF", "TIFF", "PSD", "PDF", "BMP", "JP2",
        "J2K", "JPF", "JPK", "JPM", "MJ2", "TIFF", "TIF", "PDF"
    ];
    #[Route('/applications', name: 'app_applications')]
    public function index(): Response
    {
        return $this->render('recrutements/index.html.twig', [
            'controller_name' => 'RecrutementsController',
        ]);
    }


    /**
     * @Route("/application_search_me", name="app_searchMeApplication", methods={"POST"})
     * @param Request $request
     */
    public function searchMeApplication(
        Request $request,
        ApplicationsRepository $repo
    ): Response {
        $nin = $request->request->get("search_nin");
        $cand = empty($nin) ? NULL : $repo->findOneBy(['nin' => $nin]);

        return  empty($cand) ? new JsonResponse(null, 404) :  new JsonResponse($cand);
    }



    /**
     * @Route("/apply", name="app_apply", methods={"POST"})
     * @param Request $request
     */
    public function apply(
        Request $request,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        MailerInterface $mailer,
        $application_period
    ): Response {
        $destination = $this->getParameter('app.recruitment_attachments');
        $curriculum_vitae = $request->files->get('curriculum_vitae');
        $diplomaFile = $request->files->get('diplomaFile');
        $id_card = $request->files->get('id_card');
        $experience_certificate_census_or_surveys_1 = $request->files->get('experience_certificate_census_or_surveys_1');
        $experience_certificate_census_or_surveys_2 = $request->files->get('experience_certificate_census_or_surveys_2');

        $application = new Applications();
        // $response = NULL;
        try {

            list($debut, $fin) = explode('-', $application_period);
            $currentDate =  strtotime("now");
            // $response =  new Response($currentDate >= $debut && $currentDate <= $fin ? 1 : 0);
            if ($currentDate <= $fin) {
                $entityManager->getConnection()->beginTransaction();
                $district = $entityManager->getRepository(Districts::class)->findOneBy(['id' => intval($request->request->get('posting_district'))]);
                $usualDistrict = $entityManager->getRepository(Districts::class)->findOneBy(['fdcode' => intval($request->request->get("usualDistrict"))]);
                $temporalDistrict = $entityManager->getRepository(Districts::class)->findOneBy(['fdcode' => intval($request->request->get("temporalDistrict"))]);
                $lga = $district->getLga();

                $application->setLga($lga)
                    ->setDistrict($district)
                    ->setWorkDistrict($district) // district de travail
                    ->setName(ucfirst($request->request->get("name")))
                    ->setMiddleName(ucfirst($request->request->get("middle")))
                    ->setSurname(ucfirst($request->request->get("surname"))) //2022-04-16
                    ->setBirthDate(DateTime::createFromFormat('Y-m-d', $request->request->get("birth_date")))
                    ->setSex($request->request->get("sex"))
                    ->setCurrentAddress($request->request->get("current_address"))
                    ->setUsualDistrictResidence($usualDistrict)
                    ->setTemporalDistrictResidence($temporalDistrict)
                    ->setPhone($request->request->get("phone"))
                    ->setPhone2($request->request->get("phone2"))
                    ->setPhone3($request->request->get("phone3"))
                    ->setWhatsappPhone($request->request->get("whatsappPhone"))
                    ->setNin($request->request->get("nin"))
                    ->setEmail($request->request->get("email"))
                    ->setDiploma($request->request->get("diploma"))
                    ->setProfession($request->request->get("profession"))
                    ->setLanguage1($request->request->get("language1"))
                    ->setLanguage2($request->request->get("language2"))
                    ->setLanguage3($request->request->get("language3"))
                    ->setNbrCensus(empty($request->request->get("nbr_census_or_survey")) ? 0 : intval($request->request->get("nbr_census_or_survey")))
                    ->setComputerKnowledge(intval($request->request->get("computer_knowledge")) == 1 ? TRUE : FALSE)
                    ->setCensusOrSurvey(intval($request->request->get("censuOrSurveyExperience")) == 1 ? TRUE : FALSE)
                    ->setUseOfTablet(intval($request->request->get("digitalExperience")) == 1 ? TRUE : FALSE)
                    ->setIpAddress($request->getClientIp())
                    ;

                if ($request->request->get("captcha") != NULL) {
                    $application->setCaptcha(sha1($request->request->get("captcha")));
                } else {
                    return new JsonResponse(['err' => 'Captcha is empty']);
                }
                //checks 


                if ($application->getEmail() != NULL) {
                    $email_validation_regex = "/^[a-z0-9!#$%&'*+\\/=?^_`{|}~-]+(?:\\.[a-z0-9!#$%&'*+\\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/";
                    if (0 == preg_match($email_validation_regex, $application->getEmail())) { // returns 1
                        return new JsonResponse(["err" => 'Email is incorrect'], 500);
                    }
                }

                //Choice of several languages
                $allLanguages = [$request->request->get("language1"), $request->request->get("language2"), $request->request->get("language3")];
                $allLanguages =  array_filter($allLanguages, function ($i) {
                    return $i != NULL;
                });
                foreach ($allLanguages as $oneLanguage) {
                    if (count(array_filter($allLanguages, function ($i) use ($oneLanguage) {
                        return $i == $oneLanguage;
                    })) > 1) {
                        return new JsonResponse(["err" => "The language $oneLanguage is chosen several times"], 500);
                    }
                }

                // check file name
                $allAttachedFile = [
                    // $curriculum_vitae->getClientOriginalName(),
                    $curriculum_vitae == NULL ? NULL :  $curriculum_vitae->getClientOriginalName(),
                    $diplomaFile == NULL ? NULL :  $diplomaFile->getClientOriginalName(),
                    $id_card->getClientOriginalName(),
                    $experience_certificate_census_or_surveys_1 == NULL ? NULL : $experience_certificate_census_or_surveys_1->getClientOriginalName(),
                    $experience_certificate_census_or_surveys_2 == NULL ? NULL : $experience_certificate_census_or_surveys_2->getClientOriginalName(),
                ];
                $this->checkerNameAttachedFileDuplicata($allAttachedFile);

                $application->setSubmissionNumber(uniqid('gphc'));
                $application->setNicCopy(uniqid('NIC'));

                $entityManager->persist($application);
                $entityManager->flush();

                // generate submission number
                $application->setSubmissionNumber(str_pad($application->getId(), 6, '0', STR_PAD_LEFT));

                //CV
                if ($curriculum_vitae != NULL) {
                    $result = $this->moveAttachmentAndZip($application, $curriculum_vitae, false, 'CV');
                    $application->setCv($result);
                }

                //last diploma file
                if ($diplomaFile != NULL) {
                    $result = $this->moveAttachmentAndZip($application, $diplomaFile, false, 'Diploma');
                    $application->setDiplomaFile($result);
                }

                // id_card
                $result = $this->moveAttachmentAndZip($application, $id_card, false, 'NIC');
                $application->setNicCopy($result);

                //attestation_certification

                if ($experience_certificate_census_or_surveys_1 != NULL) {
                    $result = $this->moveAttachmentAndZip($application, $experience_certificate_census_or_surveys_1, false, 'Certificate_1');
                    $application->setCensusOrServeyCertificateFile($result);
                }

                if ($experience_certificate_census_or_surveys_2 != NULL) {
                    $result = $this->moveAttachmentAndZip($application, $experience_certificate_census_or_surveys_2, false, 'Certificate_2');
                    $application->setCensusOrSurveyCertificateFile2($result);
                }

                $this->checkNbrCertificateExperiences($application);
                $application->prePersistStaff();

                $entityManager->persist($application);
                $entityManager->flush();

                try {
                    // sending mail to candidat 
                    if ($application->getEmail() != NULL) {
                        $this->sendEmail($mailer, $application, $request->request->get("captcha"));
                    } else {
                        $logger->warning(" The candidate without email with submission number: " . $application->getSubmissionNumber());
                    }
                } catch (\Exception $exception) {
                    $logger->alert("An error occurred while sending email  " . $application->getEmail() . " for submission number: " . $application->getSubmissionNumber() . " details: " . $exception->getMessage());
                }

                $entityManager->persist($application);
                $entityManager->flush();


                $entityManager->getConnection()->commit();
            } else {
                return new JsonResponse(['err' => "'Applications are closed'"], 500);
            }
        } catch (UniqueConstraintViolationException $e) {
            $entityManager->getConnection()->rollBack();
            $this->removeAllAttachedFile($application, $destination);
            return new JsonResponse(['err' => "You are already registered! Please check your application on the menu 'MY APPLICATION'"], 500);
        } catch (\Exception $e) {
            $entityManager->getConnection()->rollBack();
            $this->removeAllAttachedFile($application, $destination);
            return new JsonResponse(['err' => $e->getMessage()], 500);
        } finally {
            $logger->info(" User Agent " . $this->getUserAgent());
            $logger->info(json_encode($application));
            $logger->info("profession: " . $request->request->get("profession") . (isset($_POST['profession']) ? $_POST['profession'] : ''));
            $logger->info("work: " . $request->request->get("work") . (isset($_POST['work']) ? $_POST['work'] : ''));
        }
        return new JsonResponse($application);
    }


    /**
     * @Route("/getMyAttachments/{nin}/{sumissionNumber}/{validationCode}/{theFile}", name="get_my_attachment_candidate", methods={"GET"})
     * @param Request $request
    */
    public function getMyAttachment(
        string $validationCode,
        string $theFile,
        string $nin,
        string $sumissionNumber,
        ApplicationsRepository $repo
    ): Response {
        $notFoundResponse =   new Response('', Response::HTTP_NOT_FOUND);
        $candidacy =  $repo->findCandidatAllowToReapply($nin, $sumissionNumber, sha1($validationCode));
        if ($candidacy == NULL) {
            return $notFoundResponse;
        }
        $allFiles = [
            1 => $candidacy->getCv(),
            2 => $candidacy->getDiplomaFile(),
            3 => $candidacy->getNicCopy(),
            41 => $candidacy->getCensusOrServeyCertificateFile(),
            42 => $candidacy->getCensusOrSurveyCertificateFile2(),
        ];
        $destination = $this->getParameter('app.recruitment_attachments');

        if (isset($allFiles[$theFile])) {
            $myAttachment = "$destination/" . $allFiles[$theFile];
            if (file_exists($myAttachment)) {
                $response =  new BinaryFileResponse($myAttachment);
                $response->setContentDisposition(
                    ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                    basename($myAttachment)
                );
                return $response;
            }
        }
        return $notFoundResponse;
    }


    /**
     * @Route("/reapply/{id}", name="app_reapply", methods={"POST"})
     * @param Request $request
    */
    public function reapply(
        Applications $application,
        Request $request,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        $application_period
    ): Response {
        $curriculum_vitae = $request->files->get('curriculum_vitae');
        $diplomaFile = $request->files->get('diplomaFile');
        $id_card = $request->files->get('id_card');
        $experience_certificate_census_or_surveys_1 = $request->files->get('experience_certificate_census_or_surveys_1');
        $experience_certificate_census_or_surveys_2 = $request->files->get('experience_certificate_census_or_surveys_2');

        try {
            list($debut, $fin) = explode('-', $application_period);
            $currentDate =  strtotime("now");
            if ($currentDate <= $fin) {
                $entityManager->getConnection()->beginTransaction();
                $district = $entityManager->getRepository(Districts::class)->findOneBy(['id' => intval($request->request->get('posting_district'))]);
                $usualDistrict = $entityManager->getRepository(Districts::class)->findOneBy(['fdcode' => intval($request->request->get("usualDistrict"))]);
                $temporalDistrict = $entityManager->getRepository(Districts::class)->findOneBy(['fdcode' => intval($request->request->get("temporalDistrict"))]);
                $lga = $district->getLga();

                $application->setLga($lga)
                    ->setDistrict($district)
                    ->setDistrict($district)
                    ->setWorkDistrict($district) // district de travail
                    ->setName(ucfirst($request->request->get("name")))
                    ->setMiddleName(ucfirst($request->request->get("middle")))
                    ->setSurname(ucfirst($request->request->get("surname"))) //2022-04-16
                    ->setBirthDate(DateTime::createFromFormat('Y-m-d', $request->request->get("birth_date")))
                    ->setSex($request->request->get("sex"))
                    ->setCurrentAddress($request->request->get("current_address"))
                    ->setUsualDistrictResidence($usualDistrict)
                    ->setTemporalDistrictResidence($temporalDistrict)
                    ->setPhone($request->request->get("phone"))
                    ->setPhone2($request->request->get("phone2"))
                    ->setPhone3($request->request->get("phone3"))
                    ->setWhatsappPhone($request->request->get("whatsappPhone"))
                    ->setNin($request->request->get("nin"))
                    ->setEmail($request->request->get("email"))
                    ->setDiploma($request->request->get("diploma"))
                    ->setProfession($request->request->get("profession"))
                    ->setLanguage1($request->request->get("language1"))
                    ->setLanguage2($request->request->get("language2"))
                    ->setLanguage3($request->request->get("language3"))
                    ->setNbrCensus(empty($request->request->get("nbr_census_or_survey")) ? 0 : intval($request->request->get("nbr_census_or_survey")))
                    ->setComputerKnowledge(intval($request->request->get("computer_knowledge")) == 1 ? TRUE : FALSE)
                    ->setCensusOrSurvey(intval($request->request->get("censuOrSurveyExperience")) == 1 ? TRUE : FALSE)
                    ->setUseOfTablet(intval($request->request->get("digitalExperience")) == 1 ? TRUE : FALSE)
                    ->setIpAddress($request->getClientIp())
                    ;
                //checks 


                if ($application->getEmail() != NULL) {
                    $email_validation_regex = "/^[a-z0-9!#$%&'*+\\/=?^_`{|}~-]+(?:\\.[a-z0-9!#$%&'*+\\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/";
                    if (0 == preg_match($email_validation_regex, $application->getEmail())) { // returns 1
                        return new JsonResponse(["err" => 'Incorect E-mail'], 500);
                    }
                }

                //Choice of several languages
                $allLanguages = [$request->request->get("language1"), $request->request->get("language2"), $request->request->get("language3")];
                $allLanguages =  array_filter($allLanguages, function ($i) {
                    return $i != NULL;
                });
                foreach ($allLanguages as $oneLanguage) {
                    if (count(array_filter($allLanguages, function ($i) use ($oneLanguage) {
                        return $i == $oneLanguage;
                    })) > 1) {
                        return new JsonResponse(["err" => "The language $oneLanguage is chosen several times"], 500);
                    }
                }

                // check file name
                $allAttachedFile = [
                    // $curriculum_vitae->getClientOriginalName(),
                    $curriculum_vitae == NULL ? NULL :  $curriculum_vitae->getClientOriginalName(),
                    $diplomaFile == NULL ? NULL :  $diplomaFile->getClientOriginalName(),
                    $id_card == NULL ? NULL : $id_card->getClientOriginalName(),
                    $experience_certificate_census_or_surveys_1 == NULL ? NULL : $experience_certificate_census_or_surveys_1->getClientOriginalName(),
                    $experience_certificate_census_or_surveys_2 == NULL ? NULL : $experience_certificate_census_or_surveys_2->getClientOriginalName(),
                ];
                $this->checkerNameAttachedFileDuplicata($allAttachedFile);

                $destination = $this->getParameter('app.recruitment_attachments');

                //CV
                if ($curriculum_vitae != NULL  && $curriculum_vitae->getClientOriginalName()  != NULL) {
                    $result = $this->moveAttachmentAndZip($application, $curriculum_vitae, false, 'CV');
                    $this->removeOnAttachedFile($destination.'/'.$application->getSubmissionNumber(), $application->getCv()); // remove all file before update field
                    $application->setCv($result);
                }

                //last diploma file
                if ($diplomaFile != NULL  && $diplomaFile->getClientOriginalName()  != NULL) {
                    $result = $this->moveAttachmentAndZip($application, $diplomaFile, false, 'Diploma');
                    $this->removeOnAttachedFile($destination.'/'.$application->getSubmissionNumber(), $application->getDiplomaFile()); // remove all file before update field
                    $application->setDiplomaFile($result);
                }

                // id_card
                if ($id_card != NULL  && $id_card->getClientOriginalName()  != NULL) {
                    $result = $this->moveAttachmentAndZip($application, $id_card, false, 'NIC');
                    $this->removeOnAttachedFile($destination.'/'.$application->getSubmissionNumber(), $application->getNicCopy()); // remove all file before update field
                    $application->setNicCopy($result);
    
                }

                //attestation_certification
                if ($experience_certificate_census_or_surveys_1 != NULL  && $experience_certificate_census_or_surveys_1->getClientOriginalName()  != NULL) {
                    $result = $this->moveAttachmentAndZip($application, $experience_certificate_census_or_surveys_1, false, 'Certificate_1');
                    $this->removeOnAttachedFile($destination.'/'.$application->getSubmissionNumber(), $application->getCensusOrServeyCertificateFile()); // remove all file before update field
                    $application->setCensusOrServeyCertificateFile($result);
                }

                if ($experience_certificate_census_or_surveys_2 != NULL  && $experience_certificate_census_or_surveys_2->getClientOriginalName()  != NULL) {
                    $result = $this->moveAttachmentAndZip($application, $experience_certificate_census_or_surveys_2, false, 'Certificate_2');
                    $this->removeOnAttachedFile($destination.'/'.$application->getSubmissionNumber(), $application->getCensusOrSurveyCertificateFile2()); // remove all file before update field
                    $application->setCensusOrSurveyCertificateFile2($result);
                }

                $this->checkNbrCertificateExperiences($application);
                $application->prePersistStaff();

                $application->setSubmissionNumber(uniqid('gphc'));

                $entityManager->persist($application);
                $entityManager->flush();


                // generate submission number
                $application->setSubmissionNumber(str_pad($application->getId(), 6, '0', STR_PAD_LEFT));
                $entityManager->persist($application);
                $entityManager->flush();


                $entityManager->getConnection()->commit();
            } else {
                return new JsonResponse(['err' => "'Applications are closed'"], 500);
            }
        } catch (UniqueConstraintViolationException $e) {
            $entityManager->getConnection()->rollBack();
            return new JsonResponse(['err' => "You are already registered! Please check your application on the menu 'MY APPLICATION'"], 500);
        } catch (\Exception $e) {
            $entityManager->getConnection()->rollBack();
            return new JsonResponse(['err' => $e->getMessage()], 500);
        } finally {
            $logger->info(" User Agent " . $this->getUserAgent());
            $logger->info(json_encode($application));
            $logger->info("profession: " . $request->request->get("profession") . (isset($_POST['profession']) ? $_POST['profession'] : ''));
            $logger->info("work: " . $request->request->get("work") . (isset($_POST['work']) ? $_POST['work'] : ''));
        }
        return new JsonResponse($application);
    }


    /**
     * @Route("/allowReapply", name="app_apply_allow", methods={"POST"})
     * @param Request $request
    */
    public function allowReapply(
        Request $request,
        ApplicationsRepository $repo
    ): Response {
        $nin = $request->request->get("nin");
        $submissionNumber = $request->request->get("submissionNumber");
        $validationCode = $request->request->get("validationCode", " ");
        $valid = $repo->findCandidatAllowToReapply($nin, $submissionNumber, sha1($validationCode)) != NULL;
        return new JsonResponse(['status' => $valid]);
    }

    /**
     * check file name for several upload
     */
    private function checkerNameAttachedFileDuplicata(array $allAttachedFile)
    {
        // supprimmer les nulls
        $allAttachedFile =  array_filter($allAttachedFile, function ($i) {
            return $i != null;
        });
        foreach ($allAttachedFile as $oneAttachedFile) {
            if (count(array_filter($allAttachedFile, function ($i) use ($oneAttachedFile) {
                return $i == $oneAttachedFile;
            })) > 1) {
                throw new \Exception("The file $oneAttachedFile is already attached");
            }
        }

        $unknowExtensions =   array_filter(
            $allAttachedFile,
            function ($piece) {
                $ext = strtoupper(pathinfo($piece, PATHINFO_EXTENSION));
                return array_search($ext, self::$ALLOWED_EXTENSION) === false;
            }
        );
        if (count($unknowExtensions) > 0) {
            throw new \Exception("file extension" . (implode(",", $unknowExtensions)) . " is not recognized");
        }
    }
    
    private function checkNbrCertificateExperiences(Applications $application)
    {

        $experience_certificate_census_or_surveys =  array_filter([$application->getCensusOrServeyCertificateFile(), $application->getCensusOrSurveyCertificateFile2()], function ($attes) {
            return  $attes != null;
        });

        $nbrCensusCertificate = count($experience_certificate_census_or_surveys);

        if (
            $application->isCensusOrSurvey()
            && $application->getNbrCensus() !=  $nbrCensusCertificate
        ) {
            throw new Exception(
                "Number of census chosen " . $application->getNbrCensus() . "  different from number of certificate $nbrCensusCertificate "
            );
        }

        return true;
    }

    private function removeAllAttachedFile(Applications $application, string $destination)
    {
        //CV
        $allFiles = [
            $application->getCv(),
            $application->getDiplomaFile(),
            $application->getNicCopy(),
            $application->getCensusOrServeyCertificateFile(),
            $application->getCensusOrSurveyCertificateFile2(),
        ];
        $allFiles = array_filter($allFiles, function ($piece) {
            return  $piece !=  null;
        });
        foreach ($allFiles as $attachments) {
            if (file_exists("$destination/$attachments")) {
                unlink("$destination/$attachments");
            }
        }
    }

    private function removeOnAttachedFile(string $destination, ?string $attachments)
    {
        if ($attachments ==  null) {
            return;
        }

        $allFiles = [
            $attachments
        ];
        $allFiles = array_filter($allFiles, function ($piece) {
            return  $piece != null;
        });
        foreach ($allFiles as $attachments) {
            if (file_exists("$destination/$attachments")) {
                unlink("$destination/$attachments");
            }
        }
    }


    /**
     * @Route("/applications/list", name="app_Candidats_list", methods={"GET"})
     * @IsGranted("ROLE_USER")
    */
    public function listCandidat(
        \Knp\Component\Pager\PaginatorInterface $paginator,
        Request $request,
        ApplicationsRepository $repo
    ): Response {
        // $this->denyAccessUnlessGranted('ROLE_RECRUTEMENT');
        ini_set('memory_limit', '4096M');
        set_time_limit(0);

        if ($request->isXmlHttpRequest()) {
            $offset = $request->get('start', 0);
            $length = $request->get('length', 20);

            $query = $repo->buildDataTable($request->get('columns'), $request->get('order'));

            $qvhs = $paginator->paginate(
                $query,
                intval(($offset + 1) / $length) + 1,
                $length
            );
            return  new JsonResponse(
                [
                    "draw" => $request->get('draw', 4),
                    "recordsTotal" => ($repo->createQueryBuilder('a')->select("COUNT(a.id)")->getQuery()->getSingleScalarResult()),
                    'recordsFiltered' => $qvhs->getTotalItemCount(),
                    'data' => $qvhs->getItems()
                ]
            );
        }
        return $this->render('applications/list.html.twig', ['title' => 'All applicants', 'isCandidated' => true, 'iscom' => ($this->isGranted("ROLE_RECRUIT_COM") && !$this->isGranted("ROLE_ADMIN")) ? TRUE : FALSE,]);
    }


    /**
     * @Route("/applications/lgas/{id}", name="app_candidats_lgas", methods={"GET"})
    */
    public function applicationTableByLga(
        Lgas $lga,
        \Knp\Component\Pager\PaginatorInterface $paginator,
        Request $request,
        ApplicationsRepository $repo
    ): Response {
        $lgaUser = $this->getUser()->getLga();
        if (!$lgaUser || ($lgaUser && $lgaUser->getId() != $lga->getId())) {
            $this->createAccessDeniedException("You are not attached to this Lga");
        }

        if ($request->isXmlHttpRequest()) {
            $offset = $request->get('start', 0);
            $length = $request->get('length', 20);

            $query = $repo->buildDataTable($request->get('columns'), $request->get('order'), NULL, 0, $lga);

            $data = $paginator->paginate(
                $query,
                intval(($offset + 1) / $length) + 1,
                $length
            );
            return  new JsonResponse(
                [
                    "draw" => $request->get('draw', 4),
                    "recordsTotal" => ($repo->createQueryBuilder('a')->select("COUNT(a.id)")->getQuery()->getSingleScalarResult()),
                    'recordsFiltered' => $data->getTotalItemCount(),
                    'data' => $data->getItems()
                ]
            );
        }
        return $this->render('applications/list.html.twig', ['title' => " from " . $lga->getName(), 'isCandidated' => true, 'iscom' => $this->isGranted("ROLE_RECRUIT_COM") ? TRUE : FALSE,]);
    }


    /**
     * @Route("/applications/lga/recruit/{id}", name="app_candidats_lga_recruit", methods={"GET"})
    */
    public function applicationTableByRecruitLga(
        Lgas $lga,
        \Knp\Component\Pager\PaginatorInterface $paginator,
        Request $request,
        ApplicationsRepository $repo,
        DistrictsRepository $districtRepo,
        UserRepository $usersRepo
    ): Response {

        ini_set('memory_limit', '4096M');
        set_time_limit(0);

        // $lgaRecruitUser = $this->getUser()->getDistrict();

        $connectedUser = $usersRepo->findOneBy(["id" => $this->getUser()]);
        $lgaRecruitUser = $connectedUser->getLga();

        $title = "";

        if (!$lgaRecruitUser || ($lgaRecruitUser && $lgaRecruitUser->getId() != $lga->getId())) {
            $this->createAccessDeniedException("You are not attached to this Lga ");
        }

        if ($request->isXmlHttpRequest()) {
            $offset = $request->get('start', 0);
            $length = $request->get('length', 20);

            $filteredValue = "";
            if ($request->get('columns')[1]['data'] && $request->get('columns')[1]['search']['value']) {
                $filteredValue = $request->get('columns')[1]['search']['value'];
            }

            $query = [];
            if ($this->isGranted("ROLE_COORDINATION") || $this->isGranted("ROLE_ADMIN")) {
                $query = $repo->buildDataTable($request->get('columns'), $request->get('order'), NULL, NULL, NULL);
            } else {
                    $query = $repo->buildDataTable(
                        $request->get('columns'),
                        $request->get('order'),
                        NULL,
                        0,
                        $lga
                    );
            }

            $qvhs = $paginator->paginate(
                $query,
                intval(($offset + 1) / $length) + 1,
                $length
            );
            return  new JsonResponse(
                [
                    "draw" => $request->get('draw', 4),
                    "recordsTotal" => ($repo->createQueryBuilder('a')->select("COUNT(a.id)")->getQuery()->getSingleScalarResult()),
                    'recordsFiltered' => $qvhs->getTotalItemCount(),
                    'data' => $qvhs->getItems()
                ]
            );
        }

                $title = "List of applicants | LGA | " . $lga->getName() . " | " . $lga->getCode();
                $dist = $districtRepo->findLgaDist($lga->getCode());


        return $this->render('applications/list.html.twig', [
            'title' => $title,
            'isCandidated' => true,
            'lga' => $lga,
            'iscom' => $this->isGranted("ROLE_RECRUIT_COM") ? TRUE : FALSE,
            'communes' => $dist,
        ]);
    }


    /**
     * @Route("/applications/lga/recruit/{id}/unselected", name="app_candidats_lga_recruit_unselected", methods={"GET"})
    */
    public function candidatsUnselectedLga(
        Lgas $lga,
        \Knp\Component\Pager\PaginatorInterface $paginator,
        Request $request,
        ApplicationsRepository $repo,
        LgasRepository $lgaRepo,
        UserRepository $usersRepo,
        DistrictsRepository $distsRepo
    ): Response {

        $connectedUser = $usersRepo->findOneBy(["id" => $this->getUser()]);

            $dists = $distsRepo->findLgaDist($connectedUser->getLga()->getCode());

            $distsStringArray =  array_map(function ($dist) {
                return $dist->getFdcode();
            }, $dists);

            // var_dump($distsStringArray); die;
        if ($request->isXmlHttpRequest()) {
            $offset = $request->get('start', 0);
            $length = $request->get('length', 20);

            $query = [];
                $query = $repo->buildDataTable(
                    $request->get('columns'),
                    $request->get('order'),
                    NULL,
                    '0',
                    $lga,
                    NULL,
                    NULL,
                    $distsStringArray,
                );

            $qvhs = $paginator->paginate(
                $query,
                intval(($offset + 1) / $length) + 1,
                $length
            );
            return  new JsonResponse(
                [
                    "draw" => $request->get('draw', 4),
                    "recordsTotal" => ($repo->createQueryBuilder('a')->select("COUNT(a.id)")->getQuery()->getSingleScalarResult()),
                    'recordsFiltered' => $qvhs->getTotalItemCount(),
                    'data' => $qvhs->getItems()
                ]
            );
        }

        $title = "List of candidates not yet selected | LGA | " . $connectedUser->getLga()->getName() . " | " . $connectedUser->getLga()->getCode();

        return $this->render('applications/unselected_candidats.html.twig', [
            'iscom' => $this->isGranted("ROLE_RECRUIT_COM") ? TRUE : FALSE,
            'title' => $title,
            'isCandidated' => false,
            'lgas' => $lgaRepo->findBy(['id' => $lga->getId()], ['code' => 'ASC']),
            'communesWork' => $distsStringArray
        ]);
    }


    #[Route('/applications/atics', name: 'app_candidats_atics', methods: ['GET'])]
    public function applicationTableByAtic(

        \Knp\Component\Pager\PaginatorInterface $paginator,
        Request $request,
        ApplicationsRepository $repo
    ): Response {
        $poste = "Assistants Informaticien";
        $this->denyAccessUnlessGranted('ROLE_RH');


        if ($request->isXmlHttpRequest()) {
            $offset = $request->get('start', 0);
            $length = $request->get('length', 20);

            $query = $repo->buildDataTable($request->get('columns'), $request->get('order'), NULL, 0, NULL, $poste);

            $qvhs = $paginator->paginate(
                $query,
                intval(($offset + 1) / $length) + 1,
                $length
            );
            return  new JsonResponse(
                [
                    "draw" => $request->get('draw', 4),
                    "recordsTotal" => ($repo->createQueryBuilder('a')->select("COUNT(a.id)")->getQuery()->getSingleScalarResult()),
                    'recordsFiltered' => $qvhs->getTotalItemCount(),
                    'data' => $qvhs->getItems()
                ]
            );
        }
        return $this->render('applications/list.html.twig', ['title' => " ATIC", 'isCandidated' => true, 'iscom' => $this->isGranted("ROLE_RECRUIT_COM") ? TRUE : FALSE,]);
    }


    /**
     * @Route("/applications/detail/{id}", name="app_Candidats_detail", methods={"GET"}, options={"expose"=true})
    */
    public function showCandidat(Applications $candidat, \WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs $breadcrumbs): Response
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            $breadcrumbs->addRouteItem("All candidates", 'app_Candidats_list');
        } elseif ($this->isGranted('ROLE_RECRUIT_COM')) {
            $breadcrumbs->addRouteItem("Selected for " . $candidat->getLga()->getName(), 'app_candidats_district_formation', ['id' => $candidat->getLga()->getId()]);
        }

        $photo = null;

        $breadcrumbs->addRouteItem($candidat->getSubmissionNumber() . " " . $candidat->getName() . " " . $candidat->getSurname(), "app_Candidats_detail", ['id' => $candidat->getId()]);
        return $this->render('applications/detail.html.twig', ['candidat' => $candidat, 'photo' => $photo]);
    }

    /**
     * @Route("/applications/upload", name="app_candidats_retenus_index", methods={"GET"})
    */
    public function indexUploadCandidat(DistrictsRepository $districtsRepo, UserRepository $userRepository): Response
    {
    
        ini_set('memory_limit', '4096M');
        set_time_limit(0);
    
        $connectedUser = $userRepository->findOneBy(["id" => $this->getUser()]);
        $districts = [];
        $title = "";
    
        $districts = $districtsRepo->findLgaDist($connectedUser->getLga()->getCode());
        $title = "| LGA | " . $connectedUser->getLga()->getName() . " | " . $connectedUser->getLga()->getCode();
         
        return $this->render('applications/upload.html.twig', ['districts' => $districts, 'title' => $title]);

    }



    /**
     * @Route("/applications/district/{id}/main", name="app_candidats_district_main", methods={"GET"})
    */
    public function candidatsMainDistrict(
        Districts $district,
        \Knp\Component\Pager\PaginatorInterface $paginator,
        Request $request,
        ApplicationsRepository $repo,
        UserRepository $usersRepo,
        \WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs $breadcrumbs
    ): Response {


        $breadcrumbs->addRouteItem("Candidate Loading Page", "app_candidats_retenus_index");
        $breadcrumbs->addRouteItem("Main list", "app_candidats_district_main", ['id' => $district->getId()]);

        $connectedUser = $usersRepo->findOneBy(["id" => $this->getUser()]);

        if ($request->isXmlHttpRequest()) {
            $offset = $request->get('start', 0);
            $length = $request->get('length', 20);

            $query = $repo->buildDataTable(
                $request->get('columns'),
                $request->get('order'),
                $district,
                1,
                NULL,
                NULL,
                NULL,
                NULL
            );

            $qvhs = $paginator->paginate(
                $query,
                intval(($offset + 1) / $length) + 1,
                $length
            );
            return  new JsonResponse(
                [
                    "draw" => $request->get('draw', 4),
                    "recordsTotal" => ($repo->createQueryBuilder('a')->select("COUNT(a.id)")->getQuery()->getSingleScalarResult()),
                    'recordsFiltered' => $qvhs->getTotalItemCount(),
                    'data' => $qvhs->getItems()
                ]
            );
        }

        return $this->render('applications/main_district.html.twig', [
            'iscom' => $this->isGranted("ROLE_RECRUIT_COM") ? TRUE : FALSE,
            'isCandidated' => false,
            'district' => $district
        ]);
    }


        
    #[Route('/applicationsSuivi/detail/{id}', name: 'app_Candidats_tracking_detail', methods: ['GET'], options: ['expose' => true])]
    public function showCandidatSuivi(Applications $candidat, Request $request, \WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs $breadcrumbs): Response
    {
        $photo = null;
        try {
            $salle = $salleRepo->findOneBy(["candidat" => $candidat]);
            $code =  $salle != NULL ? $salle->getLogin() : NULL;
            $photo = $this->getParameter("csdbPath") . "/profil_image/photo_{$code}.jpg";
            if (file_exists($photo)) {
                $photo = "photo_{$code}.jpg";
            } else {
                $photo = NULL;
            }
        } catch (Exception $ex) {
        } 
        return $this->render('applications/detail.tracking.html.twig', ['candidat' => $candidat, 'photo' => $photo, 'tarif_formation' => $this->getParameter('tarif_formation')]);
    }


    /**
     * @Route("/applications/selected", name="app_candidats_district_selected", methods={"GET"})
     *
     * @param Applications $application
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @return Response
     */
    public function selectedCandidate(
        Request $request,
        ApplicationsRepository $applicationRepo,
        \Doctrine\ORM\EntityManagerInterface $em
    ): Response {


        try {
            $numeros = array_values(array_unique($request->get('numeros'), SORT_REGULAR));

            $dt = new DateTime();
            $count = 0;
            foreach ($numeros as $numc) {
                $candidat = $applicationRepo->findOneBy(['submission_number' => $numc]);

                if ($request->get('iscandidated')) {
                    $candidat->setIsSelected(TRUE);
                } else {
                    $candidat->setIsSelected(FALSE);
                }
                $em->persist($candidat);

                $em->flush();
                $count++;
            }
        } catch (\Exception $th) {
            return new JsonResponse($th->getMessage(), 500);
        }
        return new JsonResponse($count . " enumerators were selected for training", 200);
    }


    /**
     * Recupere la lsite des AR 
     * @Route("/applications/su-ars/{id}", name="app_su_ars", methods={"GET"}, options={"expose"=true})
     *
     * @param Applications $application
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @return Response
     */
    public function superviseurARs(User $user, SallesRepository $repo): Response
    {
        // return $this->json([$user->getId()], 200);
        // $arList = $repo->findSuperviseurARs($user);
        $arList = $repo->findBy(['superviseur' => $user->getId()]);
        return new JsonResponse($arList, 200);
    }

    /**
     * Recupere la lsite des AR 
     * @Route("/users/account/{id}", name="app_get_account", methods={"GET"}, options={"expose"=true})
     *
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @return Response
     */
    public function getOneAccount(User $account): Response
    {
        return new JsonResponse($account, 200);
    }


    /**
     * @Route("/applications/lgas/{id}/formation", name="app_candidats_district_formation", methods={"GET"}, options={"expose"=true})
     *
     * @return Response
     */
    public function candidatsRetenusDistrict(
        Lgas $lga,
        \Knp\Component\Pager\PaginatorInterface $paginator,
        Request $request,
        ApplicationsRepository $repo,
        UserRepository $userRepository
    ): Response {

        ini_set('memory_limit', '4096M');
        set_time_limit(0);

        $connectedUser = $userRepository->findOneBy(["id" => $this->getUser()]);

        if ($request->isXmlHttpRequest()) {
            $offset = $request->get('start', 0);
            $length = $request->get('length', 20);

            $query = [];

          
                $query = $repo->buildDataTable($request->get('columns'), $request->get('order'), NULL, 1, $lga);

            $qvhs = $paginator->paginate(
                $query,
                intval(($offset + 1) / $length) + 1,
                $length
            );
            return  new JsonResponse(
                [
                    "draw" => $request->get('draw', 4),
                    "recordsTotal" => ($repo->createQueryBuilder('a')->select("COUNT(a.id)")->getQuery()->getSingleScalarResult()),
                    'recordsFiltered' => $qvhs->getTotalItemCount(),
                    'data' => $qvhs->getItems()
                ]
            );
        }

        $title = "List of selected candidates | LGA | " . $connectedUser->getLga()->getName() . " | " . $connectedUser->getLga()->getCode();

        return $this->render('applications/selectionnes_tabs.html.twig', [
            'iscom' => $this->isGranted("ROLE_RECRUIT_COM") ? TRUE : FALSE,
            'title' => $title,
            'isCandidated' => false,
            'lga' => $connectedUser->getLga()->getId()
        ]);
    }


    /**
     * @Route("/applications/lgas/{id}/waitingList", name="app_candidats_lga_waiting", methods={"GET"}, options={"expose"=true})
     *
     * @return Response
    */
    public function candidatsOnWaitingListLga(
        Lgas $lga,
        \Knp\Component\Pager\PaginatorInterface $paginator,
        Request $request,
        ApplicationsRepository $repo,
        DistrictsRepository $districtRepo,
        UserRepository $usersRepo
    ): JsonResponse {

        $connectedUser = $usersRepo->findOneBy(["id" => $this->getUser()]);

        $districtsStringArray = [];

        if ($request->isXmlHttpRequest()) {
            $offset = $request->get('start', 0);
            $length = $request->get('length', 20);

            $isAffected = NULL;

            $query = [];
            $query = $repo->buildDataTableReserviste($request->get('columns'), $request->get('order'), NULL, 1, $isAffected, $lga);
           

            $qvhs = $paginator->paginate(
                $query,
                intval(($offset + 1) / $length) + 1,
                $length
            );
            return  new JsonResponse(
                [
                    "draw" => $request->get('draw', 4),
                    "recordsTotal" => ($repo->createQueryBuilder('a')->select("COUNT(a.id)")->getQuery()->getSingleScalarResult()),
                    'recordsFiltered' => $qvhs->getTotalItemCount(),
                    'data' => $qvhs->getItems()
                ]
            );
        }

        $title = "Waiting list | LGA | " . $connectedUser->getLga()->getName() . " | " . $connectedUser->getLga()->getCode();

        return $this->render('applications/reserviste.html.twig', [
            'iscom' => $this->isGranted("ROLE_RECRUIT_COM") ? TRUE : FALSE,
            'title' => $title,
            'isCandidated' => false,
            'lgas' => $lga
        ]);
    }



    #[Route('/applications/confirmations', name: 'app_candidats_confirmations', methods: ['GET'])]
    public function candidatsAConfirmer(
        \Knp\Component\Pager\PaginatorInterface $paginator,
        Request $request,
        ApplicationsRepository $repo
    ): Response {
        if ($request->isXmlHttpRequest()) {
            $offset = $request->get('start', 0);
            $length = $request->get('length', 20);

            // $confirmation = "";

            // if ($request->get('columns')[11]['data'] && $request->get('columns')[11]['search']['value']){
            //     $confirmation =  $request->get('columns')[11]['search']['value'];
            // }


            // if (empty($confirmation)) {
            //     $confirmation = "NULL";
            // }

            // $confirmation = $confirmation === 'true' ? true : ($confirmation === 'false' ?   false : "NULL");

            $query = $repo->buildDataTable($request->get('columns'), $request->get('order'), NULL, 1, NULL, 'Agents Recenseurs',  NULL);

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
        return $this->render('applications/confirmations.html.twig', [
            'title' => "A Confirmé",
            'isCandidated' => false
        ]);
    }

    #[Route('/applications/atics/formation', name: 'app_candidats_atics_formation', methods: ['GET'])]
    public function candidatsRetenusAtics(
        \Knp\Component\Pager\PaginatorInterface $paginator,
        Request $request,
        ApplicationsRepository $repo
    ): Response {
        if ($request->isXmlHttpRequest()) {
            $offset = $request->get('start', 0);
            $length = $request->get('length', 20);
            $query = $repo->buildDataTable($request->get('columns'), $request->get('order'), NULL, 1, NULL, 'Assistants Informaticien');
            $qvhs = $paginator->paginate(
                $query,
                intval(($offset + 1) / $length) + 1,
                $length
            );
            return  new JsonResponse(
                [
                    "draw" => $request->get('draw', 4),
                    "recordsTotal" => ($repo->createQueryBuilder('a')->select("COUNT(a.id)")->getQuery()->getSingleScalarResult()),
                    'recordsFiltered' => $qvhs->getTotalItemCount(),
                    'data' => $qvhs->getItems()
                ]
            );
        }
        return $this->render('applications/list.html.twig', [
            'title' => "ATICS retenues pour la formation",
            'isCandidated' => false,
            'iscom' => $this->isGranted("ROLE_RECRUIT_COM") ? TRUE : FALSE,
        ]);
    }

    private function createZipArchive($candidats, $destZip, $overwrite = false, $kernel = NULL)
    {

        $destination = $this->getParameter('app.recruitment_attachments');
        if (file_exists($destZip) && !$overwrite) {
            return false;
        }

        if (count($candidats)) {
            $zip = new \ZipArchive();
            if ($zip->open($destZip, $overwrite ? \ZIPARCHIVE::OVERWRITE : \ZIPARCHIVE::CREATE)) {
                // recuperation des fichiers pieces jointes
                foreach ($candidats  as $candidat) {

                    $fileName = $candidat->getSubmissionNumber() . ".zip";
                    $path = $kernel->getProjectDir() . "/var/attachments";

                    if (file_exists($path . "/" . $fileName)) {
                        $names = [$candidat->getSubmissionNumber() . ".zip"];
                        // $names = [$candidat->getCv(), $candidat->getDiplomaFile(), $candidat->getCopieCni(), $candidat->getCertificatResidence(), $candidat->getAttestationExperience(), $candidat->getAttestationCertification(), $candidat->setCensusOrServeyCertificateFile(), $candidat->getAttestationExperienceCensus Or Surveys2(), $candidat->getAttestationExperienceEnquetes1(), $candidat->getAttestationExperienceEnquetes2(), $candidat->getAttestationExperienceEnquetes3()];
                        // remove null value
                        $names = array_filter($names,  function ($name) {
                            return $name != NULL && !empty($name);
                        });
                        $finder = Finder::create()
                            ->in($destination)
                            ->depth(0)
                            ->name($names)
                            ->sortByChangedTime();

                        $filesNames = iterator_to_array($finder);
                        $allAttachedFile =  array_values(array_map(function ($f) {
                            return $f->getRelativePathname();
                        }, $filesNames));

                        $allAttachedFile =  array_map(function ($p) use ($destination) {
                            return $destination . '/' . $p;
                        }, $allAttachedFile);

                        $validFiles = array();
                        if (is_array($allAttachedFile)) {
                            foreach ($allAttachedFile as $file) {
                                if (file_exists($file)) {
                                    $validFiles[] = $file;
                                }
                            }
                        }

                        $dirname = $candidat->getSubmissionNumber() . '_' . $candidat->getName() . ' ' . $candidat->getSurname();
                        // $zip->addEmptyDir($dirname);
                        foreach ($validFiles as $file) {
                            // dump($file);
                            $zip->addFile($file, basename($file));
                        }
                    }
                }
                //fin de la recuperation des pieces joints
                $zip->close();
                return file_exists($destination);
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    private function moveAttachmentAndZip(Applications $application,  $uploaded_file, bool $overwrite = false, $type = 'OtherFile'): string
    {

        $destination = $this->getParameter('app.recruitment_attachments') .'/'. $application->getSubmissionNumber();
        // move the uploaded file
        $fileName = $type. '_' .$application->getNin() . '.' . $uploaded_file->getClientOriginalExtension();
        $uploaded_file->move($destination, $fileName);

        // zip the file
        $srcZip =  "$destination/$fileName";
        if (file_exists("$srcZip.zip") && !$overwrite) {
            throw new \Exception("$srcZip.zip  File already Exist");
        }
        $zip = new \ZipArchive();
        if ($zip->open("$srcZip.zip", $overwrite ? \ZIPARCHIVE::OVERWRITE : \ZIPARCHIVE::CREATE)) {
            $zip->addFile($srcZip, basename($srcZip));
            $zip->close();
            if (file_exists("$srcZip.zip")) {
                unlink($srcZip);
                return "$fileName.zip";
            }
        }
        throw new \Exception(" $fileName cannot be created : failed  to save  ");

        // var_dump($application->getSubmissionNumber()); die;

        //$destination = $this->getParameter('app.recruitment_attachments') .'/'. $application->getSubmissionNumber();
        // $destination = $this->getParameter('app.recruitment_attachments');
        // // move the uploaded file
        // $fileName = $type . '_' . $application->getNin() . '_' . uniqid();
        // $uploaded_file->move($destination, $fileName);

        // // zip the file
        // $srcZip =  "$destination/$fileName";
        // if (file_exists("$srcZip.zip") && !$overwrite) {
        //     throw new \Exception("$srcZip.zip  File already Exist");
        // }
        // $zip = new \ZipArchive();
        // if ($zip->open("$srcZip.zip", $overwrite ? \ZIPARCHIVE::OVERWRITE : \ZIPARCHIVE::CREATE)) {
        // $zip->addFile($srcZip, substr($srcZip, strlen($destination) + 1) /*basename($srcZip)*/);
        //     $zip->close();
        //     if (file_exists("$srcZip.zip")) {
        //         unlink($srcZip);
        //         return "$fileName.zip";
        //     }
        // }
        // throw new \Exception(" $fileName cannot be created : failed  to save ");
    }

    
    #[Route('/applications/districts/{id}/export', name: 'app_candidats_district_export', methods: ['GET'])]
    public function ExportapplicationByDistrict(
        Districts $district,
        ApplicationsRepository $repo,
        Request $request
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_RECRUTEMENT');

        $lgaRecruitUser = $this->getUser()->getDistrict();
        if (!$lgaRecruitUser || ($lgaRecruitUser && $lgaRecruitUser->getId() != $district->getId())) {
            $this->createAccessDeniedException("Vous n'etes pas rattaché à ce district ");
        }

        // si c'est un profil SRSD, on redirige vers la page de téléchargement par région
        if ($this->isGranted("ROLE_SRSD")) {
            // dd($this->getUser()->getDistrict()->getLga()->getId());
            return $this->redirectToRoute("app_candidats_lga_export", ['id' => $this->getUser()->getDistrict()->getLga()->getId()]);
        }


        $candidats = $repo->findBy(['posteSouhaite' => 'Agents Recenseurs', 'district' => $district], ['score' => 'DESC', 'district' => 'ASC']);



        $fileName = "Listes_des_candidats_district_" . $district->getSurname() . ".xlsx";
        $spreadsheet = new Spreadsheet();
        $writer = new Xlsx($spreadsheet);

        $sheet = $spreadsheet->getActiveSheet();

        $this->exportExcelApplication($candidats, $request->getSchemeAndHttpHost() . $request->getBaseUrl(), $sheet);

        $response = new StreamedResponse();
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->setCallback(function () use ($writer) {
            $writer->save('php://output');
        });
        return $response;
    }




    public function exportExcelApplication($candidats, string  $baseUrl, $sheet)
    {
        //Nom des colonnes en première lignes
        // le \n à la fin permets de faire un saut de ligne, super important en CSV
        // le point virgule sépare les données en colonnes
        $myVariableCSV = "Score;Submission number;Name;Surname;Lga;District;Date of birth;Age;Address during census;Diploma;Profession;Language1;Language2;Language3;Computer Knowledge;Census Or Survey;Use of tablet; Number Census or Survey;Files";
        $colonnesExcel = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S'];
        $i = 1;
        foreach ($colonnesExcel as $col) {
            $sheet->getColumnDimension($col)->setWidth(20);
        }
        foreach (explode(';', $myVariableCSV) as $col) {
            $colonneExcel = $colonnesExcel[$i - 1];
            $sheet->setCellValue("$colonneExcel" . '1', $col);
            $i++;
        }
        //Adding datas
        $i = 2;
        foreach ($candidats as $candidat) {
            $score = $candidat->getScore();
            $submissionNumber = $candidat->getSubmissionNumber();
            // $nin =  $candidat->getNin();
            // $poste = $candidat->getPosteSouhaite();
            $name = $candidat->getName() .' '. $candidat->getMiddleName();
            $surname = $candidat->getSurname();
            $lga = $candidat->getLga()->getName();
            $district = $candidat->getDistrict()->getName();
            $addressDuringCensus =  $candidat->getTemporalDistrictResidence()->getName();
            $birthDate = $candidat->getBirthDate()->format('d-m-Y');
            $today = new DateTime();
            $diff = $today->diff($candidat->getBirthDate());
            $age = (int) $diff->format('%y');
            $address = $candidat->getCurrentAddress();
            $email = $candidat->getEmail();
            $phone = $candidat->getPhone();
            $phone2 = $candidat->getPhone2();

            $diploma = $candidat->getDiploma();
            // $diploma = $candidat->getDiploma() == NULL ? "NON" : "OUI";
            $profession = in_array($candidat->getProfession(), ["1", "2", "3", "4", "5", "6",]) ? "Non renseignée" : $candidat->getProfession();
            $language1 = $candidat->getLanguage1();
            $language2 = $candidat->getLanguage2();
            $language3 = $candidat->getLanguage3();
            $computerKnowledge  = $candidat->isComputerKnowledge() != NULL ? "OUI" : "NON";
            $censusOrSurvey = $candidat->isCensusOrSurvey() != NULL ? "OUI" : "NON";
            $useOfTablet = $candidat->isUseOfTablet() != NULL ? "OUI" : "NON";
            //
            $nbrCensusOrSurvey = $candidat->getNbrCensus();

            if (!$this->isGranted("ROLE_SUPER_ADMIN") && $age >= 21 && $age <= 55) {
                $files = $baseUrl . $this->generateUrl('get_attachments_candidat', ['submissionNumber' => $candidat->getSubmissionNumber()]); 
                $myVariableCSV = "$score|$submissionNumber|$name|$surname|$lga|$district|$birthDate|$age|$addressDuringCensus|$diploma|$profession|$language1|$language2|$language3|$computerKnowledge|$censusOrSurvey|$useOfTablet|$nbrCensusOrSurvey|$files";
                $mesValeurs = explode('|', $myVariableCSV);
                for ($x = 0; $x < count($mesValeurs); $x++) {
                    if ($x == 1) {
                        $sheet->setCellValueExplicit($colonnesExcel[$x] . $i,  $mesValeurs[$x], DataType::TYPE_STRING);
                    }
                    else {
                        $sheet->setCellValue($colonnesExcel[$x] . $i, $mesValeurs[$x]);
                    }
                }

                $i++;
            }

            if ($this->isGranted("ROLE_SUPER_ADMIN") || $this->isGranted("ROLE_COORDINATION") || $this->isGranted("ROLE_ADMIN")) {
                $files = $baseUrl . $this->generateUrl('get_attachments_candidat', ['submissionNumber' => $candidat->getSubmissionNumber()]);
                $myVariableCSV = "$score|$submissionNumber|$name|$surname|$lga|$district|$birthDate|$age|$addressDuringCensus|$diploma|$profession|$language1|$language2|$language3|$computerKnowledge|$censusOrSurvey|$useOfTablet|$nbrCensusOrSurvey|$files";
                $mesValeurs = explode('|', $myVariableCSV);
                for ($x = 0; $x < count($mesValeurs); $x++) {
                    if ($x == 1) {
                        $sheet->setCellValueExplicit($colonnesExcel[$x] . $i,  $mesValeurs[$x], DataType::TYPE_STRING);
                    }
                    else {
                        $sheet->setCellValue($colonnesExcel[$x] . $i, $mesValeurs[$x]);
                    }
                }

                $i++;
            }
        }
        return $sheet;
    }

    #[Route('/applications/lgas/{id}/export', name: 'app_candidats_lga_export', methods: ['GET'])]
    public function ExportapplicationByLga(
        Lgas $lga,
        ApplicationsRepository $repo,
        Request  $request
    ): Response {

        ini_set('memory_limit', '4096M');
        set_time_limit(0);

        $this->denyAccessUnlessGranted('ROLE_RECRUTEMENT');
        $lgaUser = $this->getUser()->getDistrict()->getLga();
        if (!$lgaUser || ($lgaUser && $lgaUser->getId() != $lga->getId())) {
            $this->createAccessDeniedException("Vous n'etes pas rattaché à cette lga ");
        }


        $candidats = $repo->findBy(['posteSouhaite' => 'Agents Recenseurs', 'lga' => $lga], ['score' => 'DESC']);

        $fileName = "Listes_des_candidats_lga_" . $lga->getSurname() . ".xlsx";
        $spreadsheet = new Spreadsheet();
        $writer = new Xlsx($spreadsheet);

        $sheet = $spreadsheet->getActiveSheet();

        $this->exportExcelApplication($candidats, $request->getSchemeAndHttpHost() . $request->getBaseUrl(), $sheet);

        $response = new StreamedResponse();
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->setCallback(function () use ($writer) {
            $writer->save('php://output');
        });
        return $response;
    }
    #[Route('/applications/atics/export', name: 'app_candidats_atics_export', methods: ['GET'])]
    public function ExportapplicationByAtics(
        ApplicationsRepository $repo,
        Request $request
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_RECRUTEMENT');
        $this->denyAccessUnlessGranted('ROLE_RH');

        $candidats = $repo->findBy(['posteSouhaite' => 'Assistants Informaticien'], ['score' => 'DESC']);

        $fileName = "Listes_des_candidats_atics.xlsx";
        $spreadsheet = new Spreadsheet();
        $writer = new Xlsx($spreadsheet);

        $sheet = $spreadsheet->getActiveSheet();

        $this->exportExcelApplication($candidats, $request->getSchemeAndHttpHost() . $request->getBaseUrl(), $sheet);

        $response = new StreamedResponse();
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->setCallback(function () use ($writer) {
            $writer->save('php://output');
        });
        return $response;
    }


    #[Route('/selectionner/atics/export', name: 'app_selectionner_atics_export', methods: ['GET'])]
    public function ExportapplicationSelectionnerByAtics(
        ApplicationsRepository $repo,
        Request $request
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_RECRUTEMENT');
        $this->denyAccessUnlessGranted('ROLE_RH');

        $candidats = $repo->findBy(['posteSouhaite' => 'Assistants Informaticien', 'isSelected' => 1], ['score' => 'DESC']);

        $fileName = "Listes_des_candidats_atics.xlsx";
        $spreadsheet = new Spreadsheet();
        $writer = new Xlsx($spreadsheet);

        $sheet = $spreadsheet->getActiveSheet();

        $this->exportExcelApplication($candidats, $request->getSchemeAndHttpHost() . $request->getBaseUrl(), $sheet);

        $response = new StreamedResponse();
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->setCallback(function () use ($writer) {
            $writer->save('php://output');
        });
        return $response;
    }

    #[Route('/selectionner/district/{id}/export', name: 'app_selectionner_district_export', methods: ['GET'], options: ['expose' => true])]
    public function exportapplicationSelectionnerByDistrict(
        Districts  $district,
        ApplicationsRepository $repo,
        Request $request,
        UserRepository $userRepository
    ): Response {
        ini_set('memory_limit', '4096M');
        set_time_limit(0);

        $this->denyAccessUnlessGranted('ROLE_RECRUTEMENT');
        //$this->denyAccessUnlessGranted('ROLE_CTR'); 
        $connected = $userRepository->findOneBy(["id" => $this->getUser()]);

        $candidats = [];
        $fileName = "";
        if ($connected->getCustomArrnd() == NULL) {

            if ($request->get('niveau') == 'main') {
                $candidats = $repo->findBy(['district' => $district, 'posteSouhaite' => 'Agents Recenseurs', 'isSelected' => 1], ['score' => 'DESC']);
                $fileName = "Liste_Principale_des_candidats_district_" . $district->getSurname() . "_" . $district->getCode() . ".xlsx";
            } else if ($request->get('niveau') == 'attente') {
                $candidats = $repo->findBy(['district' => $district, 'posteSouhaite' => 'Agents Recenseurs', 'isReserviste' => 1], ['score' => 'DESC']);
                $fileName = "Liste_d_Attente_des_candidats_district_" . $district->getSurname() . "_" . $district->getCode() . ".xlsx";
            }
        } else if ($connected->getCustomArrnd() != NULL) {
            $districtsStringArray = [];
            if ($connected->getCustomArrnd()) {
                $homeWorks = $connected->getCustomArrnd()->getCustomArrondissementCommunes();
                $districtsStringArray =  array_map(function ($district) {
                    return $district->getCacr()->getCode();
                }, $homeWorks->toArray());
            }

            if ($request->get('niveau') == 'main') {
                $candidats = $repo->getCandidatsSheet(1, NULL, $districtsStringArray);
                // $candidats = $repo->findBy(['district' => $district, 'posteSouhaite' => 'Agents Recenseurs', 'isSelected' => 1, 'cav' => $connected->getCav()], ['score' => 'DESC']);
                $fileName = "Liste_Principale_des_candidats_Arrd_" . $connected->getCustomArrnd()->getSurname() . ".xlsx";
            } else {
                $candidats = $repo->getCandidatsSheet(NULL, 1, $districtsStringArray);
                // $candidats = $repo->findBy(['district' => $district, 'posteSouhaite' => 'Agents Recenseurs', 'isReserviste' => 1, 'cav' => $connected->getCav()], ['score' => 'DESC']);
                $fileName = "Liste_d_Attente_des_candidats_Arrd_" . $connected->getCustomArrnd()->getSurname() . ".xlsx";
            }
        }

        $spreadsheet = new Spreadsheet();
        $writer = new Xlsx($spreadsheet);

        $sheet = $spreadsheet->getActiveSheet();

        $this->exportExcelApplication($candidats, $request->getSchemeAndHttpHost() . $request->getBaseUrl(), $sheet);

        $response = new StreamedResponse();
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->setCallback(function () use ($writer) {
            $writer->save('php://output');
        });
        return $response;
    }


    /**
     * @Route("/pv/lga/{id}/export", name="app_transcript_lga_export", methods={"GET"}, options={"expose"=true})
     *
    */
    public function pvApplicationSelectionnerByLga(
        Lgas  $lga,
        ApplicationsRepository $repo,
        DistrictsRepository $districtsRepo,
        KernelInterface $kernel,
        UserRepository $userRepository
    ) {

        ini_set('memory_limit', '4096M');
        set_time_limit(0);


        $connected = $userRepository->findOneBy(["id" => $this->getUser()]);
        $nomCommission = "";
        $retenu = "";
        $lelga = "";
        $districtsStringArray = [];

            $nomCommission = "FOR LGA : <br/>" . $lga->getName();
            $retenu = " <br/>Lga : " . $lga->getName();
            $lelga = "the Lga of " . $lga->getName();
       

        $pdf = new CensusmpPdf($kernel, 'ENUMERATORS RECRUITMENT TRANSCRIPT <br/> OF CENSUS, 2024');
        $pdf->addTable([], [],  "", "Enumerators recruitment transcript of Census, 2024.", "");

        $lgaName = str_pad($lga->getName(), 5, ".", STR_PAD_BOTH);
        $an = date('Y');
        switch ($an) {
            case 2022:
                $an = "two thousand twenty-two";
                break;
            case 2023:
                $an = "two thousand twenty-three";
                break;
            case 2024:
                $an = "two thousand twenty-four";
                break;
            default:
                # code...
                break;
        }

        $pDistrict = "";

        $html = <<<EOD
            <style> p { font-size: 13px;}</style>
            <table>
                <tr>
                    <td colspan="1" rowspan="1">
                        <p class="c25"> <b>Local Government Area:</b> $lgaName</p>
                        $pDistrict
                    </td>
                </tr>
            </table>
            <br>
            <br>
            <hr>
        EOD;
        $pdf->writeHTML($html);

        $isPresentsHtml = <<<EOD
        <style> td, th { text-align: center; font-weight: bold;}</style>
            <table border="1" border="1" cellspacing="0" cellpadding="5">
                <tr>
                    <th>TITRE</th>
                    <th>PRENOM (S) ET NOM</th>
                    <th>FONCTION</th>
                </tr>
                <tr>
                    <td>Président</td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td rowspan="6" style="margin"><br><br><br><br><br>Membres</td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>Appui RH ANSD</td>
                    <td></td>
                    <td></td>
                </tr>
            </table>
        EOD;

        $html2 =  <<<EOD
            <style> p { font-size: 13px; } .mef { line-height: 220%; } </style>
            <p class="mef">L’an  {$an}, le………………………, s’est réunie la Commission de recrutement $retenu créée par arrêté n°…………. du…………………. pour procéder à la <br/> sélection des candidats au poste
             d'agent recenseur selon les critères consignés par l’Agence nationale de la Statistique et de la Démographie (ANSD).
            </p>
            <p><u>Etaient présents :</u> <br/></p>
            <p>$isPresentsHtml</p>
        EOD;
        $pdf->writeHTML($html2);

        $districtsDept = [];
        $nomfichier = "";

        if ($connected->getCustomArrnd() == NULL) {
            $districtsDept = $districtsRepo->findDeptCacrs($lga->getCode());
            $nomfichier = 'PV_DES_CANDIDATS_DE_LA_COMMISSION_DEPT_' . $lga->getNom() . "_" . $lga->getCode();
        } else {
            $districtsDept = $districtsRepo->findCacrByCodes($districtsStringArray);
            $nomfichier = 'PV_DES_CANDIDATS_DE_LA_COMMISSION_COMMUNE_Arrd_' . $connected->getCustomArrnd()->getNom();
        }

        foreach ($districtsDept as $com) {
            // TODO: SELECTIONNES
            $pdf->AddPage();
            // $pdf->Footer("ANSD-RGPH-5 / Commune de ".$com->getNom());
            $candidats = $repo->findBy(
                [
                    // 'lga' => $lga,
                    'cacrWork' => $com,
                    'posteSouhaite' => 'Agents Recenseurs',
                    'isSelected' => 1,
                    // 'isReserviste' => false
                ],
                ['nom' => 'ASC']
            );

            // $candidats = $repo->listePrincipake($lga, NULL, $com);

            $data = [];
            $i = 1;
            foreach ($candidats as $candidat) {

                $data[] = ['<span style="text-align:center;">' . $i++ . '</span>', ucfirst($candidat->getPrenom()), strtoupper($candidat->getNom()), '<span style="text-align:center;">' . $candidat->getDateNaissance()->format('d-m-Y') . '</span>', strtoupper($candidat->getLieuNaissance()), substr_replace($candidat->getNin(), '****', -4),];
            }

            $commune =  str_pad($com->getNom(), 5, ".", STR_PAD_BOTH);
            if ($connected->getCustomArrnd() == NULL) {
                $cav = str_pad($com->getCommuneArrondissementVille()->getNom(), 5, ".", STR_PAD_BOTH);
            } else {
                $cav = str_pad($connected->getCustomArrnd()->getNom(), 5, ".", STR_PAD_BOTH);
            }

            $arrndDetail = "";
            if ($connected->getCustomArrnd() != NULL) {
                $arrndDetail = " <p> <b>Arrondissement:</b> $cav </p>";
            }

            $htmlx = <<<EOD
                <style> p { font-size: 13px;}</style>
                    <table>
                        <tr>
                            <td colspan="1" rowspan="1">
                                <p> <b>Département:</b> $lgaName </p>
                                $arrndDetail
                                <p> <b>Commune:</b> $commune </p>
                                <p> <b><u>Liste Principale</u></b></p>
                            </td>
                        </tr>
                    </table>
            EOD;

            $pdf->writeHTML($htmlx);

            $headers = ["N°", "PRENOM", "NOM", "Date de naissance", "Lieu de Naissance", "CNI"];
            $pdf->SetY($pdf->getY() + 5);
            $pdf->addTable($headers, $data,  "", "ANSD-RGPH-5/L.P/" . $com->getNom());

            // TODO: LISTE ATTENTE
            $pdf->AddPage();
            // $pdf->Footer("ANSD-RGPH-5 / Commune de ".$com->getNom());
            $candidatsAttente = $repo->findBy(
                [
                    // 'lga' => $lga,
                    'cacrWork' => $com,
                    'posteSouhaite' => 'Agents Recenseurs',
                    'isReserviste' => 1
                ],
                ['score' => 'DESC']
            );

            $data = [];
            $i = 1;
            foreach ($candidatsAttente as $candidat) {

                $data[] = ['<span style="text-align:center;">' . $i++ . '</span>', ucfirst($candidat->getPrenom()), ucfirst($candidat->getNom()), '<span style="text-align:center;">' . $candidat->getDateNaissance()->format('d-m-Y') . '</span>', $candidat->getLieuNaissance(), substr_replace($candidat->getNin(), '****', -4),];
            }

            $htmlx = <<<EOD
                <style> p { font-size: 13px;}</style>
                    <table>
                        <tr>
                            <td colspan="1" rowspan="1">
                                <p> <b>Département:</b> $lgaName </p>
                                $arrndDetail
                                <p> <b>Commune:</b> $commune </p>
                                <p> <b><u>Liste d'Attente</u></b></p>
                            </td>
                        </tr>
                    </table>
            EOD;
            $pdf->writeHTML($htmlx);

            $headers = ["N°", "Prénom", "Nom", "Date de Naissance", "Lieu de Naissance", "CNI"];
            $pdf->SetY($pdf->getY() + 5);
            $pdf->addTable($headers, $data,  "", "ANSD-RGPH-5/L.A/" . $com->getNom());
            // fin
        }

        $pdf->AddPage();

        $pdf->addTable([], [],  "", "Procès-verbal de recrutement des agents recenseurs dans le cadre du RGPH-5, 2024.", "");

        $html2 =  <<<EOD
        <style> p { font-size: 13px; } .mef { line-height: 220%; } </style>
        <p class="mef">A l’issue des travaux de la Commission de recrutement, les candidats listés, ci-dessus, ont été retenus pour la formation dans $lelga.</p>
        EOD;
        $pdf->writeHTML($html2);

        $com =  $connected->getCustomArrnd() != NULL ?  $connected->getCustomArrnd()->getNom() :  $connected->getLga()->getNom();
        $dtcourant = new \Datetime();
        $df = $dtcourant->format('d/m/Y');
        $pdf->writeHTML("<br/><br/>Fait à $com, le $df <br/><br/>", true, false, false, false, "R");
        $pdf->writeHTML('<u style="font-size: 14px;">Ont signé :</u><br/>', true, false, false, false, "C");

        // Signataires
        $signataires = <<<EOD
        <style>  td { font-size: 14px;} th { text-align: center; font-weight: bold;}</style>
            <table border="1" border="1" cellspacing="0" cellpadding="10">
                <tr>
                    <th>TITRE</th>
                    <th>PRENOM (S) ET NOM</th>
                    <th>FONCTION</th>
                    <th>EMARGEMENT</th>
                </tr>
                <tr>
                    <td><u>Président:</u></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td><u>Coodonnateur <br/>administratif <br/>départemental</u></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td><u>Un Commissaire</u></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </table>
        EOD;
        $pdf->writeHTML($signataires);

        return $pdf->Output($nomfichier . '.pdf', 'I');
    }

    /**
     * @Route("/candidats/update", name="candidat_update", methods={"GET", "POST"})
     * @IsGranted("ROLE_USER")
     */
    public function updateCandidat(Request $request, ApplicationsRepository $applicationRepo,  EntityManagerInterface $em): Response
    {
        if ($request->getMethod() == "POST") {

            $updatedUser = $applicationRepo->findOneBy(['id' => $request->get('_id')]);

            $updatedUser
                ->setName($request->get('_name'))
                ->setSurname($request->get('_surname'))
                ->setTelephone($request->get('_phone'))
                ->setAddress($request->get('_address'))
                ->setEmail($request->get('_email'))
                ->setNin($request->get('_cni'))
                ->setLieuNaissance($request->get('_lieunaiss'))
                ->setDateNaissance(new DateTime($request->get('_datenaiss')))
                ->setUpdateAt(new DateTime());

            $em->persist($updatedUser);
            $em->flush();
        }

        return $this->json([], 200);
    }


    /**
     * 
     * @Route("/applications/attachmentss/{submissionNumber}", name="get_attachments_candidat" ,options={"expose"=true} )
     * @IsGranted("ROLE_USER")
     * 
     */
    public function getAttachedFileCandidat($submissionNumber, KernelInterface $kernel, ApplicationsRepository $repo, Request $request)
    {
        $candidat =  $repo->findOneBy(['submission_number' => $submissionNumber]);
        if ($candidat ==  null) {
            return new NotFoundHttpException();
        }

        // $fileName = $submissionNumber . ".zip";
        // $fileName = $submissionNumber;
        $path = $kernel->getProjectDir() . "/var/attachments" ;

            $dir = $path .'/'. $submissionNumber;

            // Initialize archive object
            $zip = new \ZipArchive();
            $zip_name = $submissionNumber . ".zip"; // Zip name
            $zip->open($zip_name, \ZipArchive::CREATE);
            
            // Create recursive directory iterator
            /** @var SplFileInfo[] $files */
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir), \RecursiveIteratorIterator::LEAVES_ONLY);
            
            foreach ($files as $name => $file) {
            // Skip directories (they would be added automatically)
            if (!$file->isDir()) {
                // Get real and relative path for current file
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($dir) + 1);
            
                // Add current file to archive
                $zip->addFile($filePath, $relativePath);
            }
            }
            
            // Zip archive will be created only after closing object
            $zip->close();
        
            //then prompt user to download the zip file
            header('Content-Type: application/zip');
            header('Content-disposition: attachment; filename=' . $zip_name);
            header('Content-Length: ' . filesize($zip_name));
            readfile($zip_name);

        // if (!file_exists($path . "/" . $zip_name)) {
        //     return new Response("<center style='padding-top:250px;'><h1>Files $submissionNumber <strong style='color: #2b4978;'>not found</h1></center>");
        // }

        // return $this->file($path . "/" . $zip_name, $zip_name, ResponseHeaderBag::DISPOSITION_INLINE);
        //cleanup the zip file
            unlink($zip_name);
    }

    /**
     *
     * @Route("/applications/attachmentssDistrict/{id}", name="get_attachments_candidat_per_district" ,options={"expose"=true} )
     * 
     * @param Request $request
     * @return Response
     **/
    public function getAttachedFileCandidatPerDistrict(Districts $district, KernelInterface $kernel, ApplicationsRepository $repo, Request $request)
    {
        ini_set('memory_limit', '4096M');
        set_time_limit(0);

        $candidats =   $repo->findBy(['posteSouhaite' => 'Agents Recenseurs', 'district' => $district], ['score' => 'DESC']);
        if (count($candidats) == 0) {
            throw new  NotFoundHttpException('Il ya pas de Candidats');
        }
        $destZip = $kernel->getCacheDir() . '/districts_' . $district->getFdcode() . '_' . uniqid() . '.zip';
        $this->createZipArchive($candidats, $destZip);

        if (file_exists($destZip)) {
            $response =  new BinaryFileResponse($destZip);
            $response->setContentDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                basename($destZip)
            );
            return $response;
        } else {
            return new Response('', Response::HTTP_NOT_FOUND);
        }
    }



    /**
     *
     * @Route("/applications/attachmentssByScore/{id}/{de}/{a}", name="get_attachments_candidat_per_score" ,options={"expose"=true} )
     * 
     * @param Request $request
     * @return Response
     **/
    public function getAttachedFileCandidatPerScore(Districts $district, int $de, int $a, ApplicationsRepository $repo, KernelInterface $kernel)
    {
        $candidats =   $repo->createQueryBuilder('c')
            ->where('c.posteSouhaite =  :poste')
            ->andWhere('c.district =  :district')
            ->andWhere('c.score BETWEEN :de AND :a')
            ->setParameter('poste', 'Agents Recenseurs')
            ->setParameter('district', $district)
            ->setParameter('de', $de)
            ->setParameter('a', $a)
            ->getQuery()
            ->getResult();

        if (count($candidats) == 0) {
            throw new  NotFoundHttpException('Il ya pas de Candidats');
        }
        $destZip = $kernel->getCacheDir() . '/districts_' . $district->getCode() . '_' . "score de_$de _ a $a _"     . uniqid() . '.zip';
        $this->createZipArchive($candidats, $destZip);

        if (file_exists($destZip)) {
            $response =  new BinaryFileResponse($destZip);
            $response->setContentDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                basename($destZip)
            );
            return $response;
        } else {
            return new Response('', Response::HTTP_NOT_FOUND);
        }
    }
    #[Route('/confirmer_application_disponibilite', name: 'confirmer_disponibilite_application', methods: ['POST'])]
    public function confirmerApplication(
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        try {
            $entityManager->getConnection()->beginTransaction();
            $id = $request->request->get("id");
            $newStatut = $request->request->get("newStatut") ===  '1';
            $candidat = $entityManager->getRepository(Applications::class)
                ->findOneBy(['id' => $id]);
            if ($candidat != NULL) {
                $candidat->setConfirmation($newStatut);
                $entityManager->persist($candidat);
                $entityManager->flush();
            }

            $entityManager->getConnection()->commit();
            return new JsonResponse($candidat);
        } catch (\Exception $e) {

            $entityManager->getConnection()->rollBack();
            return new JsonResponse(['error' => $e->getMessage(), 500]);
        }
    }

    #[Route('/is_application_period', name: 'is_application_period', methods: ['GET'])]
    public function isPeriodeApplication(
        $application_period
    ): Response {
        $response = NULL;
        try {
            list($debut, $fin) = explode('-', $application_period);
            $currentDate =  strtotime("now");
            $response =  new Response($currentDate >= $debut && $currentDate <= $fin ? 1 : 0);
        } catch (\Exception $e) {
            $response = new Response($e->getMessage(), 500);
        }
        return $response;
    }
    #[Route('/is_application_period_atic', name: 'is_application_period_atic', methods: ['GET'])]
    public function isPeriodeApplicationatic(
        $application_period_atic
    ): Response {
        $response = NULL;
        try {
            list($debut, $fin) = explode('-', $application_period_atic);
            $currentDate =  strtotime("now");
            $response =  new Response($currentDate >= $debut && $currentDate <= $fin ? 1 : 0);
        } catch (\Exception $e) {
            $response = new Response($e->getMessage(), 500);
        }
        return $response;
    }




    #[Route('/generateCsdb/application/{district}', name: 'generate_csdb_candidat_by_district', methods: ['POST'])]
    public function generateCsdbByDistrict(Districts $district, KernelInterface $kernel): JsonResponse
    {
        ini_set('memory_limit', '4096M');
        set_time_limit(0);

        $response = NULL;

        $application = new Application($kernel);
        $application->setAutoExit(false);

        // csdb login users
        $loginCmd = new ArrayInput([
            'command' => 'app:csdb:candidats', // surname de la commande
            'district' => $district->getCode(), // le code du district
        ]);

        // You can use NullOutput() if you don't need the output
        $output = new BufferedOutput();
        $content = [];

        try {
            // login final
            $exitCodeLogin =  @$application->run($loginCmd, $output);
            $content = ['msg' => $output->fetch(), 'exitCode' => $exitCodeLogin];

            $response = new JsonResponse($content);
        } catch (\Exception $e) {
            $content['erreur'] = $e->getMessage();
            $response =  new JsonResponse($content, 500);
        }
        return $response;
    }


    /**
     *
     * @Route("/getCandidatUpdate/{id}/{nin}/{sumissionNumber}/{validationCode}", name="app_get_candidat", methods={"GET"} )
     * 
     * @param Request $request
     * @return Response
    **/
    public function getCandidat(
        int $id,
        string $nin,
        string $sumissionNumber,
        $validationCode,
        ApplicationsRepository $repo
    ): Response {
        $candidat = $repo->findCandidatAllowToReapply($nin, $sumissionNumber, sha1($validationCode));
        return new JsonResponse($candidat);
    }


    #[Route('/getCandidatToConfirm/{sumissionNumber}/{captcha}', name: 'app_get_candidatToConfirm', methods: ['GET'])]
    public function getCandidatToConfirm(
        ApplicationsRepository $repo,
        Request $request,
        $sumissionNumber,
        $captcha
    ): Response {
        $candidat = [];
        // if ($request->isXmlHttpRequest()) {
        $candidat = $repo->findOneBy(['sumissionNumber' => $sumissionNumber, 'captcha' => $captcha]);
        // $candidats = $repo->findOneBy(['captcha' => $captcha]); //$candidat->isConfirmation() ===  null
        if (isset($candidat)) {
            if ($candidat->isConfirmation() ===  null) {
                return $this->render('applications/confirmationscandidat.html.twig', [
                    'candidat' => $candidat,
                    'title' => "à confirmer",
                    'isconfirme' => $candidat->isConfirmation()

                ]);
            } else {
                return $this->render('applications/confirmationscandidatconfirmer.html.twig', [
                    'candidat' => $candidat,
                    'title' => "déjà confirmé"

                ]);
                // return new JsonResponse(['err' => 'Ce candidat a déjà confirmé']);
            }
        } else {
            return $this->render('applications/confirmationscandidataucun.html.twig', [
                'candidat' => $candidat,
                'title' => "application correspondante"


            ]);
            // return new JsonResponse(['error' => 'Aucune application correspondante']);
        }
        // }


        return $this->render('applications/confirmationscandidat.html.twig', [
            'candidat' => $candidat

        ]);
    }
    
    #[Route('/candidatConfirmDisponibilite', name: 'app_get_CandidatConfirmDisponibilite', methods: ['POST'])]
    public function candidatConfirmDisponibilite(
        ApplicationsRepository $repo,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {

        //  $newStatut = $request->request->get('newStatut') ;
        $submissionNumber = $request->request->get('submissionNumber');
        // $id = $request->request->get('id') ;
        // $validationCode = $request->request->get('validationCode');
        $nin = $request->request->get('nin');

        $valid = ($repo->findCandidatAllowToapply($nin, $submissionNumber) != NULL);


        return  $valid ?  $this->confirmerApplication($request, $entityManager) : new JsonResponse(['error' => 'Incorrect validation code'], 500);
    }
    /**
     * Permet de télécharger un Backup
     * 
     * @Route("/candidats/unknow/{fileName}", name="app_candidats_unknow_file")
     * @IsGranted("ROLE_USER")
     * 
     * @return void
     */
    public function getCandidatsUnknowFile(string $fileName, KernelInterface $kernel, Request $request)
    {

        // $path = $request->get('path');
        $path = $kernel->getCacheDir() . '/var/candidats/' . $fileName;

        if (file_exists($path)) {
            return $this->file($path, null, ResponseHeaderBag::DISPOSITION_INLINE);
        } else {
            return new Response("Fichier existe pas", 404);
        }
    }

    public function sendEmail(MailerInterface $mailer, Applications $candidat, $validationCode)
    {
        $email = new TemplatedEmail();
        $email->getHeaders()
            ->addTextHeader('X-Auto-Response-Suppress', 'OOF, DR, RN, NRN, AutoReply');
        $email->from('noreply@gbos.sn');
        $email->to($candidat->getEmail());
        $email->subject($candidat->getName() . ' ' . $candidat->getMiddleName() . ' ' . $candidat->getSurname() . '\'s candidacy');
        $email->htmlTemplate('mail/candidacyNotification.html.twig');
        $email->context([
            'submissionNumber' => $candidat->getSubmissionNumber(),
            'civility' =>  $candidat->getSex() === 'M' ? 'Mr' : ($candidat->getSex() === 'F' ? 'Mrs' : 'Mrs , Mr'),
            'poste' =>  'enumerator',
            'validationCode' => $validationCode
        ]);


        try {
            $mailer->send($email);
            $candidat->setNotificationSubmissionSendAt(new \DateTimeImmutable());
        } catch (TransportExceptionInterface $e) {
            $candidat->setNotificationSubmissionSendAt(NULL);
            throw $e;
        }
    }

    public static  function getUserAgent()
    {
        try {
            $userAgent = strtolower($_SERVER['HTTP_USER_AGENT']);

            // Identify the browser. Check Opera and Safari first in case of spoof. Let Google Chrome be identified as Safari. 
            if (preg_match('/opera/', $userAgent)) {
                $name = 'opera';
            } elseif (preg_match('/webkit/', $userAgent)) {
                $name = 'safari';
            } elseif (preg_match('/msie/', $userAgent)) {
                $name = 'msie';
            } elseif (preg_match('/mozilla/', $userAgent) && !preg_match('/compatible/', $userAgent)) {
                $name = 'mozilla';
            } else {
                $name = 'unrecognized';
            }

            // What version? 
            if (preg_match('/.+(?:rv|it|ra|ie)[\/: ]([\d.]+)/', $userAgent, $matches)) {
                $version = $matches[1];
            } else {
                $version = 'unknown';
            }

            // Running on what platform? 
            if (preg_match('/linux/', $userAgent)) {
                $platform = 'linux';
            } elseif (preg_match('/macintosh|mac os x/', $userAgent)) {
                $platform = 'mac';
            } elseif (preg_match('/windows|win32/', $userAgent)) {
                $platform = 'windows';
            } else {
                $platform = 'unrecognized';
            }

            return
                "name:$name,version:$version,platform:$platform, userAgent:$userAgent ";
        } catch (\Exception $e) {
            return "User Agent non trouvé";
        }
    }



    #[Route('/applications/sendconfirmations', name: 'app_candidats_sendconfirmations', methods: ['GET'], options: ['expose' => true])]
    public function candidatsSendConfirmation(
        \Knp\Component\Pager\PaginatorInterface $paginator,
        Request $request,
        ApplicationsRepository $repo
    ): Response {
        if ($request->isXmlHttpRequest()) {
            $offset = $request->get('start', 0);
            $length = $request->get('length', 20);
            $confirmation =  $request->get('columns')[10]['search']['value'];

            if (empty($confirmation)) {
                $confirmation = "NULL";
            }

            // $confirmation = $confirmation === 'true' ? true : ($confirmation === 'false' ?   false : "NULL");

            $query = $repo->buildDataTable($request->get('columns'), $request->get('order'), NULL, 1, NULL, 'Agents Recenseurs',  $confirmation);

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
        return $this->render('applications/sendconfirmations.html.twig', [
            'title' => "A Confirmé",
            'isCandidated' => false
        ]);
    }

    #[Route('/applications/sendcandidatPourConfirmation', name: 'app_candidats_sendcandidatPourConfirmation', methods: ['GET', 'POST'])]
    public function sendcandidatPourConfirmation(
        Request $request,
        ApplicationsRepository $repo
    ) {
        $tabnumDossier = $request->get('candidats');
        $listdossier = intval(implode(', ', $tabnumDossier[0]));

        $query = $repo->buildDataTablePourConfirmer($listdossier);

        // if ($request->isXmlHttpRequest()) {
        ini_set('memory_limit', '4096M');
        set_time_limit(0);
        $request->isXmlHttpRequest();
        $ultramsg_token = "d9yqaugsiql3rpuh"; // Ultramsg.com token
        $instance_id = "instance27981"; // Ultramsg.com instance id
        $client = new WhatsAppApi($ultramsg_token, $instance_id);
        $api = [];
        foreach ($query as $value) {
            $tel = $value->getPhoneWhatsapp();
            $tel3 = substr($tel, 0, 4);
            if ($tel3 == '+221') {
                $telephon = $value->getPhoneWhatsapp();
            } else {
                $telephon = "+221" . $value->getPhoneWhatsapp();
            }
            $to = $telephon;
            $body = "Bonjour " . $value->getName() . " " . $value->getSurname() . ", vous étes selectionné(e) dans le cadre du RGPH5, Veuillez cliquer sur ce lien pour confirmer ou non votre disponibilité : https://154.65.38.2/censusmp_desurnamebrement/public/getCandidatToConfirm/" . $value->getSubmissionNumber() . "/" . $value->getCaptcha();


            $api = $client->sendChatMessage($to, $body, $priority = 10, $referenceId = "");
            // dump($api);
        }
        // dd();
        // }
        // die();
        if (!empty($api)) {
            return new JsonResponse(['success' => 'Message WhatsApp est bien envoye']);
        } else {
            return new JsonResponse(['error' => 'Verifier si vous etes a jour (payement) ou votre connexion internet SVP!'], 200);
        }

        // var_dump($api);
        // print_r($api);

    }
    #[Route('/applications/sendcandidatsmsPourConfirmation', name: 'app_candidats_sendcandidatsmsPourConfirmation', methods: ['GET', 'POST'])]
    public function sendcandidatsmsPourConfirmation(
        Request $request,
        ApplicationsRepository $repo
    ) {
        // if ($request->isXmlHttpRequest()) {
        $request->isXmlHttpRequest();
        $tabnumDossier = $request->get('candidats');

        $listdossier = intval(implode(', ', $tabnumDossier[0]));
        $query = $repo->buildDataTablePourConfirmer($listdossier);

        ini_set('memory_limit', '4096M');
        set_time_limit(0);
        $url = [];
        foreach ($query as $value) {
            $url = "http://31.207.36.17/smswebsiitav/apismssiitav.php/?sender=SIITAV&recipient=" . $value->getPhone() . "&token=1234567890&text=Bonjour " . $value->getName() . " " . $value->getSurname() . ", vous étes selectionné(e) dans le cadre du RGPH5, Veuillez cliquer sur ce lien pour confirmer ou non votre disponibilité :https://154.65.38.2/censusmp_desurnamebrement/public/getCandidatToConfirm/" . $value->getSubmissionNumber() . "/" . $value->getCaptcha();
            //  dd($url);
        }
        // dd();
        // }
        return $this->redirect($url, 301);
    }
    #[Route('/applications/confirmationspasencore', name: 'app_candidats_confirmationspasencore', methods: ['GET'])]
    public function candidatsAConfirmerPasEncore(
        \Knp\Component\Pager\PaginatorInterface $paginator,
        Request $request,
        ApplicationsRepository $repo
    ): Response {
        if ($request->isXmlHttpRequest()) {
            $offset = $request->get('start', 0);
            $length = $request->get('length', 20);
            $confirmation =  $request->get('columns')[11]['search']['value'];

            if (empty($confirmation)) {
                $confirmation = "NULL";
            }

            // $confirmation = $confirmation === 'true' ? true : ($confirmation === 'false' ?   false : "NULL");

            $query = $repo->buildDataTableConfirmer($request->get('columns'), $request->get('order'), NULL, 1, NULL, 'Agents Recenseurs',  $confirmation);

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

        return $this->render('applications/confirmationspasencore.html.twig', [
            'title' => "pas encore confirmé",
            'isCandidated' => false
        ]);
    }

    #[Route('/applications/confirmationsoui', name: 'app_candidats_confirmationsoui', methods: ['GET'])]
    public function candidatsAConfirmerOui(
        \Knp\Component\Pager\PaginatorInterface $paginator,
        Request $request,
        ApplicationsRepository $repo
    ): Response {
        if ($request->isXmlHttpRequest()) {
            $offset = $request->get('start', 0);
            $length = $request->get('length', 20);
            // $confirmation =  $request->get('columns')[10]['search']['value'];
            // $confirmation = "1";
            if (empty($confirmation)) {
                $confirmation = "1";
            }

            // $confirmation = $confirmation === 'true' ? true : ($confirmation === 'false' ?   false : "NULL");

            $query = $repo->buildDataTable($request->get('columns'), $request->get('order'), NULL, 1, NULL, 'Agents Recenseurs',  $confirmation);

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
        return $this->render('applications/confirmationsoui.html.twig', [
            'title' => "disponible",
            'isCandidated' => false
        ]);
    }

    #[Route('/applications/confirmationsnon', name: 'app_candidats_confirmationsnon', methods: ['GET'])]
    public function candidatsAConfirmerNon(
        \Knp\Component\Pager\PaginatorInterface $paginator,
        Request $request,
        ApplicationsRepository $repo
    ): Response {
        if ($request->isXmlHttpRequest()) {
            $offset = $request->get('start', 0);
            $length = $request->get('length', 20);
            // $confirmation =  $request->get('columns')[10]['search']['value'];
            $confirmation = '0';
            // if (empty($confirmation)) {
            //      $confirmation ='1';
            // }

            // $confirmation = $confirmation === 'true' ? true : ($confirmation === 'false' ?   false : "NULL");

            $query = $repo->buildDataTable($request->get('columns'), $request->get('order'), NULL, 1, NULL, 'Agents Recenseurs',  $confirmation);

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
        return $this->render('applications/confirmationsnon.html.twig', [
            'title' => "non disponible",
            'isCandidated' => false
        ]);
    }

    #[Route('/applications/sendsmsconfirmations', name: 'app_candidats_sendsmsconfirmations', methods: ['GET'], options: ['expose' => true])]
    public function candidatsSendsmsConfirmation(
        \Knp\Component\Pager\PaginatorInterface $paginator,
        Request $request,
        ApplicationsRepository $repo
    ): Response {
        ini_set('memory_limit', '4096M');
        set_time_limit(0);
        if ($request->isXmlHttpRequest()) {
            $offset = $request->get('start', 0);
            $length = $request->get('length', 20);
            $confirmation =  $request->get('columns')[10]['search']['value'];
            if (empty($confirmation)) {
                $confirmation = 1;
            }

            // $confirmation = $confirmation === 'true' ? true : ($confirmation === 'false' ?   false : "NULL");

            $query = $repo->buildDataTable($request->get('columns'), $request->get('order'), NULL, 1, NULL, 'Agents Recenseurs',  $confirmation);

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
        return $this->render('applications/sendsmsconfirmations.html.twig', [
            'title' => "à confirmer",
            'isCandidated' => false
        ]);
    }

    #[Route('/applications/sendconsultationwhatsapp', name: 'app_candidats_sendconsultationwhatsapp', methods: ['GET'])]
    public function sendconsultationwhatsapp(
        Request $request,
        ApplicationsRepository $repo
    ): Response {

        // if ($request->isXmlHttpRequest()) {
        ini_set('memory_limit', '4096M');
        set_time_limit(0);
        $request->isXmlHttpRequest();
        $ultramsg_token = "d9yqaugsiql3rpuh"; // Ultramsg.com token
        $instance_id = "instance27981"; // Ultramsg.com instance id
        $client = new WhatsAppApi($ultramsg_token, $instance_id);
        // $api=[];
        // $apistatistic=[];

        $apistatistic = $client->getMessageStatistics();

        //dump($api);

        $tabstatistic = $apistatistic["messages_statistics"];
        // dump($apistatistic["messages_statistics"]);
        // dump($apistatus);

        // }
        // dd();
        // die();
        return $this->render('applications/consultation.html.twig', [
            'title' => "à confirmer",
            'tabstatistic' => $tabstatistic,
            'isCandidated' => false
        ]);
    }


    /**
     *
     * @Route("/applications/export_all", name="app_candidats_export_all" , methods={"GET"},options={"expose"=true} )
     * 
     * @param Request $request
     * @return Response
    **/
    public function exportAll(
        ApplicationsRepository $repo,
        Request $request
    ): Response {

        ini_set('memory_limit', '4096M');
        set_time_limit(0);

        // $this->denyAccessUnlessGranted('ROLE_RECRUTEMENT');

        $candidats = $repo->findBy([], ['score' => 'DESC']);

        $fileName = "Census_Enumerators_applicants.xlsx";

        $spreadsheet = new Spreadsheet();
        $writer = new Xlsx($spreadsheet);

        $sheet = $spreadsheet->getActiveSheet();

        $this->exportExcelApplication($candidats, $request->getSchemeAndHttpHost() . $request->getBaseUrl(), $sheet);

        $response = new StreamedResponse();
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->setCallback(function () use ($writer) {
            $writer->save('php://output');
        });
        return $response;
    }



    #[Route('/gphc5/enumerators/{code}/{liste}', name: 'app_gphc5_enumerators', methods: ['GET'], options: ['expose' => true])]
    public function getSelectedEnumerators(string $code, string $liste, Request $request, ApplicationsRepository $repo): JsonResponse
    {

        ini_set('memory_limit', '4096M');
        set_time_limit(0);

        // if (!$request->headers->has('API-TOKEN') && ($request->headers->get('API-TOKEN') !== 'gphc5Enumx90')) {
        //     // throw $this->createAccessDeniedException("API Non Autorisé");
        //     return $this->json(['API Non Autorisé'], 500);
        // }

        $candidats = $repo->findSelectedEnumerators($liste, $code);

        return new JsonResponse($candidats);
    }

    #[Route('/applications/sendsmscandidatConfirmation', name: 'app_candidats_sendsmscandidatConfirmation', methods: ['GET', 'POST'])]
    public function sendsmscandidatConfirmation(
        Request $request,
        ApplicationsRepository $repo,
        SallesRepository $sallesRepo,
        KernelInterface $kernel,
        EntityManagerInterface $em
    ): Response {
        $helpLibs = $kernel->getProjectDir() . "/public/dist/libs/httpful.phar";
        include($helpLibs);
        $response = [];
        $key = [];
        $tabnumDossier = $request->get('candidats');
        $i = 1;

        try {
            $em->beginTransaction();

            foreach ($tabnumDossier as $key) {

                $query = $repo->buildDataTablePourConfirmer($key);
                ini_set('memory_limit', '4096M');
                set_time_limit(0);
                // $request->isXmlHttpRequest();
                $subject = "SMS_RGPH5";
                $signature = "GBOS RGPH5";
                $token = "94efa769d8b2149f811d2e7104ea625d";
                $api_access_key = "44f8e6588ac06b4d0e0ec0049b10b6a5";
                $login = "gbos";
                $subject = urlencode($subject);
                $signature = urlencode($signature);
                $timestamp = time();
                $nbrSmsEnvoye = 0;
                foreach ($query as $value) {
                    $infoSalle = $sallesRepo->findOneBy(['candidat' => $value->getId()]);
                    if ($infoSalle != null && $value->isConfirmation() == true) {
                        $tel = $value->getPhone();
                        $tel3 = substr($tel, 0, 4);
                        if ($value->getSex() == 'H') {
                            $sexe = 'Monsieur';
                        } else {
                            $sexe = 'Madame';
                        }
                        if ($tel3 == '+221') {
                            $telephon = $value->getPhone();
                        } else {
                            $telephon = "+221" . $value->getPhone();
                        }

                        $recipient = $telephon;
                        $content = "Bonjour, l’GBOS vous informe que la formation des agents recenseurs débute le 26 avril 2024 à 9h:00. Vous êtes invité à aller sur la plateforme de recrutement du RGPH-5 (https://recruteRGPH5.gbos.sn) ou sur le site du RGPH-5 (www.recensement.sn) pour connaître l'address de votre salle de formation. Merci";
                        $content = urlencode($content);
                        $msgToEncrypt = $token . $subject . $signature . $recipient . $content . $timestamp;
                        $key = hash_hmac('sha1', $msgToEncrypt, $api_access_key);
                        //$key=md5($msgToEncrypt.$api_access_key); //si vous utilisez MD5
                        $uri = 'https://api.orangesmspro.sn:8443/api?token=' . $token . '&subject=' . $subject . '&signature=' . $signature . '&recipient=' . $recipient . '&content=' . $content . '&timestamp=' . $timestamp . '&key=' . $key;
                        $response = \Httpful\Request::get($uri)
                            ->authenticateWith($login, $token)
                            ->send();

                        if ($response->code == 200 || str_contains($response->body, 'Message envoye')) {
                            $value->setSendSms(true);
                            $em->persist($value);
                            //STATUS_TEXT: Message envoye
                            $nbrSmsEnvoye++;
                        }
                    }
                }

                if ($nbrSmsEnvoye > 0) {
                    $em->flush();
                    $em->commit();
                }
            }
        } catch (\Throwable $th) {
            //throw $th;
            $em->rollback();
        }
        // $total = count($key);
        $total = count($tabnumDossier);
        return new JsonResponse(['success' => "$i/$total SMS envoyé à candidats avec succès"]);
    }

    // Exportation de la liste des candidats non disponible
    #[Route('/applications/export-notconfirmed', name: 'app_candidats_export_candidats_not_confirmed', methods: ['GET'], options: ['expose' => true])]
    public function exportNotConfirmed(
        ApplicationsRepository $repo,
        Request $request
    ): Response {

        ini_set('memory_limit', '4096M');
        set_time_limit(0);

        // $this->denyAccessUnlessGranted('ROLE_RECRUTEMENT');

        $candidats = $repo->findCandidatsNotConfirmed();

        $fileName = "ListeCandidats_Non_disponible.xlsx";

        $spreadsheet = new Spreadsheet();
        $writer = new Xlsx($spreadsheet);

        $sheet = $spreadsheet->getActiveSheet();

        $this->sheeetNotConfirmedTemplate($candidats, $request->getSchemeAndHttpHost() . $request->getBaseUrl(), $sheet);

        $response = new StreamedResponse();
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->setCallback(function () use ($writer) {
            $writer->save('php://output');
        });
        return $response;
    }

    public function sheeetNotConfirmedTemplate($candidats, string  $baseUrl, $sheet)
    {
        //Nom des colonnes en première lignes
        // le \n à la fin permets de faire un saut de ligne, super important en CSV
        // le point virgule sépare les données en colonnes
        $myVariableCSV = "SCORE;NUMERO_DOSSIER;PRENOM;NOM;DATE_NAISS;LIEU_NAISSANCE;REGION;DEPARTEMENT;CAV;COMMUNE_TRAVAIL;TELEPHONE";
        $colonnesExcel = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K'];
        $i = 1;
        foreach ($colonnesExcel as $col) {
            $sheet->getColumnDimension($col)->setWidth(20);
        }
        foreach (explode(';', $myVariableCSV) as $col) {
            $colonneExcel = $colonnesExcel[$i - 1];
            $sheet->setCellValue("$colonneExcel" . '1', $col);
            $i++;
        }
        //Ajout de données (avec le . devant pour ajouter les données à la variable existante)
        $i = 2;
        foreach ($candidats as $candidat) {
            $scoreEnsae = $candidat->getScoreEnsae();
            $submissionNumber = $candidat->getSubmissionNumber();
            $name = $candidat->getName();
            $surname = $candidat->getSurname();
            $lga = $candidat->getLga()->getSurname();
            $d = $candidat->getDistrict()->getSurname();
            $cav = $candidat->getCav()->getSurname();
            $district =  $candidat->getCacrWork()->getSurname();
            $birthDate = $candidat->getBirthDate()->format('d-m-Y');
            $lieuNaissance = $candidat->getLieuNaissance();
            $phone = $candidat->getPhone();
            $phone2 = $candidat->getPhone2();

            $myVariableCSV = "$scoreEnsae|$submissionNumber|$name|$surname|$birthDate|$lieuNaissance|$lga|$d|$cav|$district|$phone";
            $mesValeurs = explode('|', $myVariableCSV);
            for ($x = 0; $x < count($mesValeurs); $x++) {
                if ($x == 1) {
                    $sheet->setCellValueExplicit($colonnesExcel[$x] . $i,  $mesValeurs[$x], DataType::TYPE_STRING);
                } else {
                    $sheet->setCellValue($colonnesExcel[$x] . $i, $mesValeurs[$x]);
                }
            }

            $i++;
        }
        return $sheet;
    }
    // Exportation de la liste des candidats n'ayant pas confirmé
    #[Route('/applications/export-pasconfirmer', name: 'app_candidats_export_candidats_pas_confirmer', methods: ['GET'], options: ['expose' => true])]
    public function exportPasConfirmer(
        ApplicationsRepository $repo,
        Request $request
    ): Response {

        ini_set('memory_limit', '4096M');
        set_time_limit(0);

        // $this->denyAccessUnlessGranted('ROLE_RECRUTEMENT');

        $candidats = $repo->findCandidatsPasConfirmer();

        $fileName = "ListeCandidats_NayantPasConfirmer.xlsx";

        $spreadsheet = new Spreadsheet();
        $writer = new Xlsx($spreadsheet);

        $sheet = $spreadsheet->getActiveSheet();

        $this->sheeetPasConfirmerTemplate($candidats, $request->getSchemeAndHttpHost() . $request->getBaseUrl(), $sheet);

        $response = new StreamedResponse();
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->setCallback(function () use ($writer) {
            $writer->save('php://output');
        });
        return $response;
    }

    public function sheeetPasConfirmerTemplate($candidats, string  $baseUrl, $sheet)
    {
        //Nom des colonnes en première lignes
        // le \n à la fin permets de faire un saut de ligne, super important en CSV
        // le point virgule sépare les données en colonnes
        $myVariableCSV = "NUMERO_DOSSIER;PRENOM;NOM;DATE_NAISS;LIEU_NAISSANCE;REGION;DEPARTEMENT;CAV;COMMUNE_TRAVAIL;TELEPHONE";
        $colonnesExcel = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
        $i = 1;
        foreach ($colonnesExcel as $col) {
            $sheet->getColumnDimension($col)->setWidth(20);
        }
        foreach (explode(';', $myVariableCSV) as $col) {
            $colonneExcel = $colonnesExcel[$i - 1];
            $sheet->setCellValue("$colonneExcel" . '1', $col);
            $i++;
        }
        //Ajout de données (avec le . devant pour ajouter les données à la variable existante)
        $i = 2;
        foreach ($candidats as $candidat) {
            $submissionNumber = $candidat->getSubmissionNumber();
            $name = $candidat->getName();
            $surname = $candidat->getSurname();
            $lga = $candidat->getLga()->getSurname();
            $d = $candidat->getDistrict()->getSurname();
            $cav = $candidat->getCav()->getSurname();
            $district =  $candidat->getCacrWork()->getSurname();
            $birthDate = $candidat->getBirthDate()->format('d-m-Y');
            $lieuNaissance = $candidat->getLieuNaissance();
            $phone = $candidat->getPhone();
            $phone2 = $candidat->getPhone2();

            $myVariableCSV = "$submissionNumber|$name|$surname|$birthDate|$lieuNaissance|$lga|$d|$cav|$district|$phone";
            $mesValeurs = explode('|', $myVariableCSV);
            for ($x = 0; $x < count($mesValeurs); $x++) {
                if ($x == 1) {
                    $sheet->setCellValueExplicit($colonnesExcel[$x] . $i,  $mesValeurs[$x], DataType::TYPE_STRING);
                } else {
                    $sheet->setCellValue($colonnesExcel[$x] . $i, $mesValeurs[$x]);
                }
            }

            $i++;
        }
        return $sheet;
    }


    #[Route('/confirmer_application_disponibilite_attente', name: 'confirmer_disponibilite_application_attente', methods: ['POST'])]
    public function confirmerApplicationAttente(
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        try {
            $entityManager->getConnection()->beginTransaction();
            $id = $request->request->get("id");
            $newStatut = $request->request->get("newStatut") ===  '1';
            $candidat = $entityManager->getRepository(Applications::class)
                ->findOneBy(['id' => $id]);
            if ($candidat != NULL) {
                $candidat->setConfirmation($newStatut);
                $candidat->setIsReplace(true);
                $entityManager->persist($candidat);
                $entityManager->flush();
            }

            $entityManager->getConnection()->commit();
            return new JsonResponse($candidat);
        } catch (\Exception $e) {

            $entityManager->getConnection()->rollBack();
            return new JsonResponse(['error' => $e->getMessage(), 500]);
        }
    }

    #[Route('/applications/sendsmsremplacement', name: 'app_candidats_sendsmsremplacement', methods: ['GET'], options: ['expose' => true])]
    public function candidatsSendsmsRemplacement(
        \Knp\Component\Pager\PaginatorInterface $paginator,
        Request $request,
        ApplicationsRepository $repo
    ): Response {
        ini_set('memory_limit', '4096M');
        set_time_limit(0);
        if ($request->isXmlHttpRequest()) {
            $offset = $request->get('start', 0);
            $length = $request->get('length', 20);
            $confirmation =  $request->get('columns')[10]['search']['value'];

            if (empty($confirmation)) {
                $confirmation = "NULL";
            }

            $query = $repo->findListeAttente($request->get('columns'), $request->get('order'), NULL, 0, NULL, $confirmation, 1);

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
        return $this->render('applications/sendsmsremplacement.html.twig', [
            'title' => "à confirmer",
            'isCandidated' => false
        ]);
    }
    // Exportation de la liste des candidats n'ayant pas confirmé
    #[Route('/applications/export-listeattente', name: 'app_candidats_export_candidats_liste_attente', methods: ['GET'], options: ['expose' => true])]
    public function exportListeAttente(
        ApplicationsRepository $repo,
        Request $request
    ): Response {

        ini_set('memory_limit', '4096M');
        set_time_limit(0);

        // $this->denyAccessUnlessGranted('ROLE_RECRUTEMENT');

        $candidats = $repo->findListeAttentes();

        $fileName = "ListeCandidats_Liste_Attente.xlsx";

        $spreadsheet = new Spreadsheet();
        $writer = new Xlsx($spreadsheet);

        $sheet = $spreadsheet->getActiveSheet();

        $this->sheeetListeAttenteTemplate($candidats, $request->getSchemeAndHttpHost() . $request->getBaseUrl(), $sheet);

        $response = new StreamedResponse();
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->setCallback(function () use ($writer) {
            $writer->save('php://output');
        });
        return $response;
    }

    public function sheeetListeAttenteTemplate($candidats, string  $baseUrl, $sheet)
    {
        //Nom des colonnes en première lignes
        // le \n à la fin permets de faire un saut de ligne, super important en CSV
        // le point virgule sépare les données en colonnes
        $myVariableCSV = "NUMERO_DOSSIER;PRENOM;NOM;DATE_NAISS;LIEU_NAISSANCE;REGION;DEPARTEMENT;CAV;COMMUNE_TRAVAIL;TELEPHONE";
        $colonnesExcel = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
        $i = 1;
        foreach ($colonnesExcel as $col) {
            $sheet->getColumnDimension($col)->setWidth(20);
        }
        foreach (explode(';', $myVariableCSV) as $col) {
            $colonneExcel = $colonnesExcel[$i - 1];
            $sheet->setCellValue("$colonneExcel" . '1', $col);
            $i++;
        }
        //Ajout de données (avec le . devant pour ajouter les données à la variable existante)
        $i = 2;
        foreach ($candidats as $candidat) {
            $submissionNumber = $candidat->getSubmissionNumber();
            $name = $candidat->getName();
            $surname = $candidat->getSurname();
            $lga = $candidat->getLga()->getSurname();
            $d = $candidat->getDistrict()->getSurname();
            $cav = $candidat->getCav()->getSurname();
            $district =  $candidat->getCacrWork()->getSurname();
            $birthDate = $candidat->getBirthDate()->format('d-m-Y');
            $lieuNaissance = $candidat->getLieuNaissance();
            $phone = $candidat->getPhone();
            $phone2 = $candidat->getPhone2();

            $myVariableCSV = "$submissionNumber|$name|$surname|$birthDate|$lieuNaissance|$lga|$d|$cav|$district|$phone";
            $mesValeurs = explode('|', $myVariableCSV);
            for ($x = 0; $x < count($mesValeurs); $x++) {
                if ($x == 1) {
                    $sheet->setCellValueExplicit($colonnesExcel[$x] . $i,  $mesValeurs[$x], DataType::TYPE_STRING);
                } else {
                    $sheet->setCellValue($colonnesExcel[$x] . $i, $mesValeurs[$x]);
                }
            }

            $i++;
        }
        return $sheet;
    }

    /**
     * @Route("/candidatcopte/{id}/delete", name="app_candidat_copte_delete", methods={"GET", "POST"}, options={"expose"=true})
     * @IsGranted("ROLE_USER")
     */
    public function deleteCopte(Applications $agentCopte, Request $request, SallesRepository $salleRepo, EntityManagerInterface $em): Response
    {
        try {

            if ($request->getMethod() == "POST") {
                $estEnFormation = $salleRepo->findOneBy(['candidat' => $agentCopte]);

                if ($estEnFormation != NULL) {
                    return $this->json("Impossible de supprimer ce superviseur, il est lie à une salle de classe", 500);
                }

                $em->remove($agentCopte);
                $em->flush();
            }
        } catch (\Exception $th) {
            return $this->json($th->getMessage(), 500);
        }

        return $this->json("Candidat supprimé avec succès !", 200);
    }


}
