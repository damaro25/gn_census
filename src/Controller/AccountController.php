<?php

namespace App\Controller;
use App\Entity\User;
use App\Entity\PasswordUpdate;
use App\Form\PasswordUpdateType;
use App\Repository\UserRepository;
use App\Repository\RolesRepository;
use App\Repository\JournalRepository;
use App\Repository\RegionsRepository;
use Symfony\Component\Form\FormError;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AccountController extends AbstractController
{
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @Route("/", name="index")
     */
    public function index(Request $request): Response
    {
      
      return $this->render('account/login.html.twig');
    }

    /**
     * @Route("/login_check", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils,Request $request): Response
    {
        // var_dump('ERROR'); die;

        $error = $authenticationUtils->getLastAuthenticationError();
        // var_dump('ERROR', $error); die;

        $request->getSession('session')->getFlashBag()->clear();
        $flashBag =  $request->getSession('session')->getFlashBag();
        if ($error) {
            $flashBag->set('danger', $error->getMessageKey());
        }
        return $this->redirectToRoute('login_view');
        
    }

    /**
     * @Route("/login", name="login_view")
    */
    public function loginViewlogin(AuthenticationUtils $authenticationUtils,Request $request): Response{
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

         // last username entered by the user
         $lastUsername = $authenticationUtils->getLastUsername();
        //  var_dump($lastUsername); die;

        return $this->render('account/login.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
        ]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout(Request $request, Response $response, TokenInterface $token): Response
    {
        $request->getSession()->invalidate();
        return $this->redirectToRoute('login_view');
    }

    /**
     * Permet de modifier le mot de passe
     * 
     * @Route("/censusmp/account/password-update", name="account_password")
     * @return Response
     */
    public function updatePassword(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        UserRepository $userRepository,
        EntityManagerInterface $em
    ): Response {
        $passwordUpdate = new PasswordUpdate();

        // récupère l'utilisateur courant ou connecté
        $user =  $this->getUser();

        $form = $this->createForm(PasswordUpdateType::class, $passwordUpdate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if (!password_verify($passwordUpdate->getOldPassword(), $user->getPassword())) {
                $form->get('oldPassword')->addError(new FormError("your old password is not the correct one"));
            } else {

                $hash = $userPasswordHasher->hashPassword($user, $passwordUpdate->getNewPassword());

                $currentUser = $userRepository->findOneBy(['id' => $this->getUser()]);
                $currentUser->setPassword($hash);
                $currentUser->setPasswordView($passwordUpdate->getNewPassword());
                $em->persist($currentUser);
                $em->flush();

                $this->addFlash(
                    'success',
                    'Your Password has been successfully changed, please log in again !'
                );

                return $this->redirectToRoute('app_logout');
            }
        }

        return $this->render('account/password.html.twig', [
            'form' => $form->createView()
        ]);
    }


    /**
     * retourne la liste des utilisateurs 
     *
     * @Route("/censusmp/users/all", name="index_utilisateur")
     * @IsGranted("ROLE_USER")
     * 
     * @param UserRepository $userRepository
     * @param Request $request
     * @return void
     */
    public function getUserAllUser(RegionsRepository $regionRepo,
        PaginatorInterface $paginator ,
        UserRepository $repo, 
        Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $offset = $request->get('start', 0);
            $length = $request->get('length', 20);
            
            $columns = $request->get('columns') ;

            // $role = NULL;
            // if(!empty($request->get('columns')[8]['data']) && $request->get('columns')[8]['search']['value']){
            //     $role = $request->get('columns')[8]['data'];
            // }

            $query = $repo->buildAllDataTable($columns, $request->get('order'));

            $allUsers = $paginator->paginate(
                $query,
                intval(($offset + 1) / $length) + 1,
                $length
            );
            return  new JsonResponse(
                [
                    "draw" => $request->get('draw', 4),
                    "recordsTotal" => ($repo->createQueryBuilder('a')->select("COUNT(a.id)")->getQuery()->getSingleScalarResult()),
                    'recordsFiltered' => $allUsers->getTotalItemCount(),
                    'data' => $allUsers->getItems()
                ]
            );
        }
        $regions = $regionRepo->findBy([], ['code' => "ASC"]);
        return $this->render('account/all_users.html.twig', [
            'regions' => $regions,
        ]);

    }

    /**
     * retourne la liste des actions des utilisateurs
     *
     * @Route("/censusmp/journal/actions", name="get_journal_actions", options={"expose"=true})
     * @IsGranted("ROLE_USER")
     * 
     * @param UserRepository $userRepository
     * @param Request $request
     * @return void
     */
    public function getJournalActions(
        PaginatorInterface $paginator ,
        JournalRepository $repo, 
        Request $request,
        \WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs $breadcrumbs)
    {
        $user=$request->get('user');
        $objectClass = $request->get('objectClass');
        $objectId = $request->get('objectId');

        if($user != NULL){
            $breadcrumbs->addRouteItem("Tout le journal", "get_journal_actions");
            $breadcrumbs->addRouteItem("Journal d'action pour $user", "get_journal_actions");
        }elseif($objectClass != NULL && $objectId != NULL){
            $breadcrumbs->addRouteItem("Tout le journal", "get_journal_actions");
            $breadcrumbs->addRouteItem("Journal d'action sur  $objectClass [ $objectId ] ", "get_journal_actions");
        }

        if ($request->isXmlHttpRequest()) {
            $offset = $request->get('start', 0);
            $length = $request->get('length', 20);

          

            $query = $repo->buildDataTable($request->get('columns'), $request->get('order'), $user ,$objectClass , $objectId);

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
                    'data' => $this->convertToJson($qvhs->getItems())
                ]
            );
        }
        return $this->render('account/journal.html.twig');
    }
    
    function convertToJson( $journals){

        return array_map(function($journal) {
            
            return [
                'id'=>$journal->getId(),
                'action'=>$journal->getAction(), 
                'loggedAt'=>$journal->getLoggedAt()->format('d F Y H:i:s'),
                'objectId'=>$journal->getObjectId(),
                'objectClass'=> str_replace("App\\Entity\\",'',$journal->getObjectClass()),
                'version'=> $journal->getVersion(),
                'username'=>$journal->getUsername(),
                'data'=>json_encode($journal->getData())
             ];
        } , $journals) ;
    }

     /**
     * Profil
     *
     * @Route("/profil", name="app_user_profil", methods={"GET"})
     * @IsGranted("ROLE_USER")
     * 
     * @return Response
     */
    public function profile()
    {
        return $this->render('users/profil.html.twig');
    }

     /**
     * Permet d'enregistrer une mensualité
     * 
     * @Route("/profil/{id}/update", name="app_profil_update", methods={"GET", "POST"}, options={"expose"=true})
     * @IsGranted("ROLE_USER")
     */
    public function profileUpdate(
        User $profil,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        if ($request->getMethod() == "POST") {
            try {
                $profil
                    ->setPrenom($request->get('_prenom'))
                    ->setNom($request->get('_nom'))
                    ->setMail($request->get('_email'))
                    ->setTelephone($request->get('_telephone'))
                    ->setAdresse($request->get('_adresse'))
                    ->setUpdateAt(new \DateTime())
                ;

                $em->persist($profil);
                $em->flush();
            } catch (\Exception $th) {
                // return $this->json($th->getMessage(), 500);
            }
        }
        return $this->redirectToRoute("app_user_profil");
    }
}
