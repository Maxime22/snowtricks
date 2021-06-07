<?php

namespace App\Controller;


use App\Entity\Trick;
use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomepageController extends BaseController
{
    /**
     * @Route("/", name="home")
     */
    public function index(): Response
    {
        // https://stackoverflow.com/questions/21499296/doctrine-fetchall-with-limits/21499394
        $tricks = $this->em->getRepository(Trick::class)->findBy([], null, 10);

        return $this->render('homepage/index.html.twig', [
            'tricks' => $tricks
        ]);
    }

    /**
     * @Route("/getMoreTricks", name="getMoreTricks", methods={"POST"})
     */
    public function getMoreTricks(Request $request)
    {
        $offset = $request->request->get('offset');
        $newTricks = $this->em->getRepository(Trick::class)->findBy([],null,10,$offset);

        return $this->json(
            ['newTricks' => $newTricks],
            200,
            [],
            ['groups' => 'trick']
        );
    }
}
