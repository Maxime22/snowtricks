<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
use App\Service\FileUploader;
use App\Repository\TrickRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController{

    /**
     * @Route("/profile/{id}", name="profile")
     */
    public function index(User $user, Request $request, TrickRepository $trickRepository, FileUploader $fileUploader): Response
    {
        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        $tricks = $trickRepository->findBy(['author'=>$this->getUser()->getId()]);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $photoFile = $form->get('photoFile')->getData();
            if ($photoFile) {
                $oldFile = null;
                if ($user->getPhoto()) {
                    $oldFile = new File($this->getParameter('userUpload_directory') . "/" . $user->getPhoto());
                }
                $newFilename = $fileUploader->upload($photoFile, $this->getParameter('userUpload_directory'), $oldFile);
                $user->setPhoto($newFilename);
            }

            $user->setUpdatedAt();
            $em->flush();
            $this->addFlash('notice', 'Profil sauvegardé');
            return $this->redirectToRoute('profile', ['id'=>$user->getId()]);
        }

        return $this->render('user/index.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'tricks' => $tricks
        ]);
    }

}