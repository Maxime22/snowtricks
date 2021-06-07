<?php

namespace App\Controller;


use App\Entity\Trick;
use App\Repository\TrickRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomepageController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(TrickRepository $trickRepository): Response
    {
        // https://stackoverflow.com/questions/21499296/doctrine-fetchall-with-limits/21499394
        $tricks = $trickRepository->findBy([], null, 10);

        return $this->render('homepage/index.html.twig', [
            'tricks' => $tricks
        ]);
    }

    /**
     * @Route("/getMoreTricks", name="getMoreTricks", methods={"POST"})
     */
    public function getMoreTricks(Request $request, TrickRepository $trickRepository)
    {
        $offset = $request->request->get('offset');
        $newTricks = $trickRepository->findBy([],null,10,$offset);

        return $this->json(
            ['newTricks' => $newTricks],
            200,
            [],
            ['groups' => 'trick']
        );
    }
}
