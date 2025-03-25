<?php

namespace App\Controller;

use App\Entity\Classroom;
use App\Entity\User;
use App\Utils\Utils;
use App\Repository\ApplicationsRepository;
use App\Repository\ClassroomRepository;
use App\Repository\LgasRepository;
use App\Repository\UserRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/teams')]
class CensusManageTeamsController extends AbstractController
{

    #[Route('', name: 'app_users_superviseurs', methods: ['GET'], options: ['expose' => true])]
    #[IsGranted('ROLE_USER')]
    public function index(
        Request $request,
        UserRepository $repo,
        LgasRepository $lgasRepository,
        PaginatorInterface $paginator
    ): Response {

        $connectedSupervisorLogin = NULL;
        if (in_array('ROLE_SUPERVISOR', $this->getUser()->getRoles())) {
            $connectedSupervisorLogin = $this->getUser()->getUsername();
        }

        if ($request->isXmlHttpRequest()) {
            $offset = $request->get('start', 0);
            $length = $request->get('length', 20);

            $query = $repo->buildDataTable($request->get('columns'), $request->get('order'), 'ROLE_SUPERVISOR', $connectedSupervisorLogin);

            $users = $paginator->paginate(
                $query,
                intval(($offset + 1) / $length) + 1,
                $length
            );

            return  new JsonResponse(
                [
                    "draw" => $request->get('draw', 4),
                    "recordsTotal" => ($repo->createQueryBuilder('a')->select("COUNT(a.id)")->getQuery()->getSingleScalarResult()),
                    'recordsFiltered' => $users->getTotalItemCount(),
                    'data' => $users->getItems()
                ]
            );
        }

        $lgas = $lgasRepository->findAll();
        return $this->render('users/supervisors.html.twig', ['lgas' => $lgas]);
    }

    #[Route('/save', name: 'app_users_superviseurs_save', methods: ['POST'], options: ['expose' => true])]
    #[IsGranted('ROLE_USER')]
    public function saveSp(
        Request $request,
        \Doctrine\ORM\EntityManagerInterface $defaultEntityManager = NULL,
        UserPasswordHasherInterface $userPasswordHasher,
        UserRepository $repo,
        LgasRepository $lgasRepository
    ): JsonResponse {
        try {
            $defaultEntityManager->beginTransaction();

            $dt = new \DateTime();
            // $guid = Uuid::v6();
            $uuid = Utils::generateCaseID();
            $randomPassword = random_int(1000, 9999) . "@" . $dt->format('Y');

            $sp = new User();

            if (!empty($request->get('_slug'))) {
                $sp = $repo->findOneBy(['slug' => $request->get('_slug')]);
            }

            $lga = $lgasRepository->findOneBy(['id' => $request->get('_lga')]);
            $sp
                ->setname($request->get('_firstName'))
                ->setsurname($request->get('_lastName'))
                ->setPhone($request->get('_phone'))
                ->setLga($lga);

            if (empty($request->get('_slug'))) {
                $login = $repo->findNextSpLogin();

                if ($login != NULL) {
                    $next = $login + 1;
                    $login = "SP" . str_pad($next, 4, "0", STR_PAD_LEFT);
                }

                $hashPassword = $userPasswordHasher->hashPassword($sp, $randomPassword);
                $sp
                    ->setRoles(array("ROLE_SUPERVISOR"))
                    ->setUsername($login ?? 'SP0101')
                    ->setPassword($hashPassword)
                    ->setPasswordView($randomPassword)
                    ->setCreateAt($dt)
                    ->setUpdateAt($dt)
                    ->setUuid($uuid)
                    // ->setUuid($guid->toRfc4122())
                    ->setIsActived(true);
            } else {
                $sp->setUpdateAt($dt);
            }

            $defaultEntityManager->persist($sp);
            $defaultEntityManager->flush();
            $defaultEntityManager->commit();

            return $this->json("Superviseur ajouté avec succès !");
        } catch (\Exception $th) {
            $defaultEntityManager->rollback();
            return $this->json($th->getMessage(), 500);
        }
    }

    #[Route('/delete/{slug}', name: 'app_users_superviseurs_remove', methods: ['GET'], options: ['expose' => true])]
    #[IsGranted('ROLE_USER')]
    public function removeSp(User $sp, \Doctrine\ORM\EntityManagerInterface $defaultEntityManager = NULL): JsonResponse
    {
        try {
            $defaultEntityManager->remove($sp);
            $defaultEntityManager->flush();
            return $this->json("Formateur supprimé avec succès ");
        } catch (\Exception $th) {
            return $this->json($th->getMessage(), 500);
        }
    }

    #[Route('/{slug}/manage', name: 'app_sp_team', methods: ['GET'], options: ['expose' => true])]
    #[IsGranted('ROLE_USER')]
    public function spTeam(
        User $sp,
        Request $request,
        ApplicationsRepository $repo,
        PaginatorInterface $paginator,
        \WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs $breadcrumbs
    ): Response {
        $breadcrumbs->addRouteItem("Liste superviseurs", "app_users_superviseurs");
        $breadcrumbs->addRouteItem("Salle " . $sp->getUsername(), "app_sp_team", ['slug' => $sp->getSlug()]);


        if ($request->isXmlHttpRequest()) {
            $offset = $request->get('start', 0);
            $length = $request->get('length', 20);

            // $query = $repo->buildDataTable($request->get('columns'), $request->get('order'), $sp);
            $query = [];

            $candidats = $paginator->paginate(
                $query,
                intval(($offset + 1) / $length) + 1,
                $length
            );

            return  new JsonResponse(
                [
                    "draw" => $request->get('draw', 4),
                    // "recordsTotal" => ($repo->createQueryBuilder('a')->select("COUNT(a.id)")->getQuery()->getSingleScalarResult()),
                    "recordsTotal" => 0,
                    'recordsFiltered' => $candidats->getTotalItemCount(),
                    'data' => $candidats->getItems()
                ]
            );
        }
        return $this->render('team/create.html.twig', ['sp' => $sp]);
    }

    #[Route('/unaffected-candidats', name: 'app_unaffected_project_candidats', methods: ['GET'], options: ['expose' => true])]
    #[IsGranted('ROLE_USER')]
    public function unAffectedCandidats(
        Request $request,
        ApplicationsRepository $repo,
        PaginatorInterface $paginator,
    ): Response {

        $offset = $request->get('start', 0);
        $length = $request->get('length', 20);

        $query = $repo->getUnAffectedCandidat($request->get('columns'), $request->get('order'));

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

    #[Route('/{slug}/create-team', name: 'app_affect_enumerators_to_sp', methods: ['POST'], options: ['expose' => true])]
    #[IsGranted('ROLE_USER')]
    public function save(
        User $supervisor,
        Request $request,
        \Doctrine\ORM\EntityManagerInterface $defaultEntityManager = NULL,
        ClassroomRepository $eqRepository,
        ApplicationsRepository $enumeratorRepo
    ): JsonResponse {
        try {
            $defaultEntityManager->beginTransaction();


            $candidatIds = $request->get('_candidatIds');
            $countSave = 0;

            $lastUsername = $eqRepository->findLastEnumeratorUsername(substr($supervisor->getUserName(), 2, 4));
            foreach ($candidatIds as $candId) {
                $guid = Utils::generateCaseID();
                $dt = new \DateTime();

                $enumerator = $enumeratorRepo->findOneBy(['id' => $candId]);
                if ($enumerator != NULL) {

                    $isAdd = $eqRepository->findOneBy(['enumerator' => $enumerator, 'supervisor' => $supervisor]);
                    if ($isAdd) {
                        break 1;
                    }

                    $newAgent = $eqRepository->findOneBy(['enumerator' => $enumerator]);
                    if ($newAgent != NULL) {
                        $newAgent->setDeleteAt($dt);
                    } else {
                        $newAgent = new Classroom();
                        // $nextUsername = null;
                        if ($lastUsername == null) {
                            $lastUsername = substr($supervisor->getUserName(), 2, 4) . "01";
                        } else {
                            $next = intval($lastUsername) + 1;
                            $lastUsername = str_pad($next, 6, "0", STR_PAD_LEFT);
                        }
                    }

                    $newAgent
                        ->setSupervisor($supervisor)
                        ->setEnumerator($enumerator)
                        ->setUsername($lastUsername)
                        ->setPassword(random_int(1000, 9999))
                        ->setCreateAt($dt)
                        ->setUpdateAt($dt)
                        ->setUuid($guid)
                        ->setDeleted(0)
                        ->setOpsaisi($this->getUser());

                    $defaultEntityManager->persist($newAgent);

                    $enumerator
                        ->setIsAffected(true)
                        ->setUpdateAt($dt);
                    $defaultEntityManager->persist($enumerator);

                    $countSave++;
                }
            }

            $defaultEntityManager->flush();
            $defaultEntityManager->commit();

            return $this->json("$countSave candidat(s) affecté(s) au superviseur " . $supervisor->getUsername());
        } catch (\Exception $th) {
            $defaultEntityManager->rollback();
            return $this->json($th->getMessage(), 500);
        }
    }

    #[Route('/list/{slug}', name: 'sp_team_list', methods: ['GET'], options: ['expose' => true])]
    #[IsGranted('ROLE_USER')]
    public function spTeamList(
        User $supervisor,
        Request $request,
        ClassroomRepository $repo,
        PaginatorInterface $paginator,
    ): Response {

        $offset = $request->get('start', 0);
        $length = $request->get('length', 20);

        $query = $repo->buildDatatable($request->get('columns'), $request->get('order'), $supervisor);

        $enumerators = $paginator->paginate(
            $query,
            intval(($offset + 1) / $length) + 1,
            $length
        );

        return  new JsonResponse(
            [
                "draw" => $request->get('draw', 4),
                "recordsTotal" => ($repo->createQueryBuilder('a')->select("COUNT(a.id)")->getQuery()->getSingleScalarResult()),
                'recordsFiltered' => $enumerators->getTotalItemCount(),
                'data' => $enumerators->getItems()
            ]
        );
    }

    #[Route('/{slug}/remove', name: 'remove_enumerator_from_classroom', methods: ['POST'], options: ['expose' => true])]
    #[IsGranted('ROLE_USER')]
    public function removeFromSalle(Classroom $salle, \Doctrine\ORM\EntityManagerInterface $defaultEntityManager = NULL): JsonResponse
    {
        try {
            $defaultEntityManager->beginTransaction();

            $dt = new \DateTime();

            $salle->getEnumerator()
                ->setUpdateAt($dt)
                ->setIsAffected(NULL);

            $defaultEntityManager->persist($salle->getEnumerator());
            $defaultEntityManager->remove($salle);

            $defaultEntityManager->flush();
            $defaultEntityManager->commit();

            return $this->json("Enumerator is removed from classroom");
        } catch (\Exception $th) {
            $defaultEntityManager->rollback();
            return $this->json($th->getMessage(), 500);
        }
    }
}
