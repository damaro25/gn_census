<?php

namespace App\Controller;

use App\Entity\Applications;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LanguagesController extends AbstractController
{
    /**
     * @Route("/languages", name="app_languages", methods={"GET"}, options={"expose"=true})
    */
    public function index(): Response
    {
        // var_dump('ICI'); die;
        return new JsonResponse(Applications::LANGUAGES);
    }
}
