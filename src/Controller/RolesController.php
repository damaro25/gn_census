<?php

namespace App\Controller;

use App\Entity\Roles;
use App\Form\RolesType;
use App\Repository\RolesRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/rolescensusmp")
 */
class RolesController extends AbstractController
{

    /**
     * @Route("/", name="roles_index", methods={"GET"})
     * @IsGranted("ROLE_USER")
     */
    public function indexXmlRoles(PaginatorInterface $paginator,
        RolesRepository $rolesRepository , 
        Request $request,
        SerializerInterface $serializer
    ): Response
    {
        if($request->isXmlHttpRequest()) {
            $roles  = $rolesRepository->findAll();
            $data =  $serializer->serialize($roles, JsonEncoder::FORMAT);
            return new JsonResponse($data ==  NULL ? NULL: $data, Response::HTTP_OK, [], true);
        }

        $query = $rolesRepository->createQueryBuilder('a')
            ->getQuery();
        $roles = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            20

        );
        return $this->render('role_censusmp/index.html.twig', [
            'roles' => $roles,
        ]);
    }



    /**
     * @Route("/", name="role_censusmp_index", methods={"GET"})
     * @IsGranted("ROLE_USER")
     */
    public function index(
        RolesRepository $rolesRepository,
        Request $request,
        PaginatorInterface $paginator
    ): Response {
        $query = $rolesRepository->createQueryBuilder('a')
            ->getQuery();
        $roles = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            20

        );
        return $this->render('role_censusmp/index.html.twig', [
            'roles' => $roles,
        ]);
    }

    /**
     * @Route("/new", name="role_censusmp_new", methods={"GET","POST"})
     * @IsGranted("ROLE_USER")
     */
    public function new(ManagerRegistry $doctrine, Request $request): Response
    {
        $role = new Roles();
        $form = $this->createForm(RolesType::class, $role);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $doctrine->getManager();
            $entityManager->persist($role);
            $entityManager->flush();

            return $this->redirectToRoute('role_censusmp_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('role_censusmp/new.html.twig', [
            'role' => $role,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="role_censusmp_show", methods={"GET"})
     * @IsGranted("ROLE_USER")
     */
    public function show(Roles $role): Response
    {
        return $this->render('role_censusmp/show.html.twig', [
            'role' => $role,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="role_censusmp_edit", methods={"GET","POST"},options={"expose"=true})
     * @IsGranted("ROLE_USER")
     */
    public function edit(ManagerRegistry $doctrine, Request $request, Roles $role): Response
    {
        $form = $this->createForm(RolesType::class, $role);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $doctrine->getManager()->flush();

            return $this->redirectToRoute('role_censusmp_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('role_censusmp/edit.html.twig', [
            'role' => $role,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="role_censusmp_delete", methods={"POST"},options={"expose"=true})
     * @IsGranted("ROLE_USER")
     */
    public function delete(ManagerRegistry $doctrine,
        \App\Repository\UserRepository $userRepository,
        Request $request,
        Roles $role
    ): Response {
        if ($userRepository->isUserUseThisRole($role)) {
            $msg = 'Ce profil ' . $role->getSurname() . ' est en cours d\'utilisation par au moins un utilisateur.';
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['erreur' => $msg], Response::HTTP_FORBIDDEN);
            }
            throw  $this->createAccessDeniedException($msg);
        }

        if ($this->isCsrfTokenValid('delete' . $role->getId(), $request->request->get('_token'))) {
            $entityManager = $doctrine->getManager();
            $entityManager->remove($role);
            $entityManager->flush();
        }

        return $this->redirectToRoute('role_censusmp_index', [], Response::HTTP_SEE_OTHER);
    }
}
