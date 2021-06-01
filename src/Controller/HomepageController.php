<?php

namespace App\Controller;


use App\Entity\Trick;
use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomepageController extends BaseController
{
    /**
     * @Route("/", name="home")
     */
    public function index(): Response
    {
        $tricks = $this->em->getRepository(Trick::class)->findAll();

        return $this->render('homepage/index.html.twig', [
            'tricks' => $tricks
        ]);
    }
}
