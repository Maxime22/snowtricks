<?php

namespace App\Controller;

use App\Entity\Trick;
use App\Entity\User;
use App\Form\TrickType;
use App\Service\FileUploader;
use App\Repository\TrickRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @Route("/trick")
 */
class TrickController extends BaseController
{
    /**
     * @Route("/trick/new", name="trick_new", methods={"GET","POST"})
     */
    public function new(Request $request, FileUploader $fileUploader): Response
    {
        $trick = new Trick();

        // TODO : change the user when the login is done
        $author = $this->em->getRepository(User::class)->find(1);

        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);

        // TODO : delete dd
        // dd($form->createView()->children["photos"]->vars["prototype"]);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var UploadedFile $mainImgFile */
            $mainImgFile = $form->get('mainImg')->getData();

            /** @var UploadedFile[] $photoFiles */
            $photoFiles = $form->get('photosFiles')->getData();

            $videos = $form->get('videos')->getData();

            if ($mainImgFile) {
                $newFilename = $fileUploader->upload($mainImgFile, $this->getParameter('trickUpload_directory'));
                $trick->setMainImgName($newFilename);
            }

            if (count($photoFiles) > 0) {
                foreach ($photoFiles as $photoFile) {
                    $newFilename = $fileUploader->upload($photoFile, $this->getParameter('trickUpload_directory'));
                    $trick->addPhoto($newFilename);
                }
            }

            $trick->setAuthor($author);
            $trick->setVideos($videos);
            $trick->setCreatedAt();
            $this->em->persist($trick);
            $this->em->flush();

            return $this->redirectToRoute('home');
        }

        return $this->render('trick/new.html.twig', [
            'trick' => $trick,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/trick/{id}/{slug}", name="trick_show", methods={"GET"})
     */
    /* public function show(Trick $trick): Response
    {
        return $this->render('trick/show.html.twig', [
            'trick' => $trick,
        ]);
    } */

    /**
     * @Route("/trick/{id}/edit", name="trick_edit", methods={"GET","POST"})
     */
    /* public function edit(Request $request, Trick $trick): Response
    {
        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('trick_index');
        }

        return $this->render('trick/edit.html.twig', [
            'trick' => $trick,
            'form' => $form->createView(),
        ]);
    } */

    /**
     * @Route("/trick/{id}", name="trick_delete", methods={"POST"})
     */
    /* public function delete(Request $request, Trick $trick): Response
    {
        if ($this->isCsrfTokenValid('delete'.$trick->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($trick);
            $entityManager->flush();
        }

        return $this->redirectToRoute('trick_index');
    } */
}
