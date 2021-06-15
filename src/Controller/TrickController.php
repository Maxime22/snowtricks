<?php

namespace App\Controller;

use App\Entity\Trick;
use App\Entity\Comment;
use App\Form\TrickType;
use App\Form\CommentType;
use App\Service\FileUploader;
use App\Repository\UserRepository;
use App\Repository\CommentRepository;
use App\Repository\ImageRepository;
use App\Service\CheckAndPersistTrickForm;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class TrickController extends AbstractController
{
    /**
     * @Route("/trick/new", name="trick_new", methods={"GET","POST"})
     * @IsGranted("ROLE_USER")
     */
    public function new(Request $request, FileUploader $fileUploader, UserRepository $userRepository, CheckAndPersistTrickForm $checkAndPersistTrickForm): Response
    {
        $trick = new Trick();

        $author = $userRepository->find($this->getUser());
        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $checkAndPersistTrickForm->persistValidForm($request, $form, $trick, ["author" => $author, "arrayPhotoNames" => null]);
            return $this->redirectToRoute('home');
        }

        return $this->render('trick/new.html.twig', [
            'trick' => $trick,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/trick/{slug}/{id}", name="trick_show", methods={"GET","POST"}, requirements={"id":"\d+","slug":"[a-z0-9\-]*"})
     */
    public function show(Trick $trick, Request $request, CommentRepository $commentRepository): Response
    {
        $em = $this->getDoctrine()->getManager();
        $comments = $commentRepository->findBy(['trick' => $trick], ["createdAt" => "DESC"]);

        $newComment = new Comment();
        $formComment = $this->createForm(CommentType::class, $newComment);
        $formComment->handleRequest($request);
        if ($formComment->isSubmitted() && $formComment->isValid()) {
            $newComment->setAuthor($this->getUser());
            $newComment->setCreatedAt();
            $newComment->setTrick($trick);
            $em->persist($newComment);
            $em->flush();
            $this->addFlash('notice', 'Commentaire ajouté');
            return $this->redirectToRoute('trick_show', ['id' => $trick->getId(), 'slug' => $trick->getSlug()]);
        }

        return $this->render('trick/show.html.twig', [
            'trick' => $trick,
            'formComment' => $formComment->createView(),
            'comments' => $comments
        ]);
    }

    /**
     * @Route("/trick/{id}/edit", name="trick_edit", methods={"GET","POST"}, requirements={"id":"\d+"})
     * @IsGranted("ROLE_USER")
     */
    public function edit(Request $request, Trick $trick, ImageRepository $imageRepository, CheckAndPersistTrickForm $checkAndPersistTrickForm): Response
    {
        $images = $imageRepository->findBy(['trick' => $trick]);

        $arrayPhotoNames = null;
        foreach ($images as $image) {
            $arrayPhotoNames[] = $image->getPath();
            $trick->addImage($image);
        }

        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $checkAndPersistTrickForm->persistValidForm($request, $form, $trick, ["author" => null, "arrayPhotoNames" => $arrayPhotoNames]);
            return $this->redirectToRoute('home');
        }

        return $this->render('trick/edit.html.twig', [
            'trick' => $trick,
            'form' => $form->createView(),
            'arrayPhotosForPreview' => isset($arrayPhotoNames) ? json_encode($arrayPhotoNames) : ''
        ]);
    }

    /**
     * @Route("/trick/{id}/delete", name="trick_delete", requirements={"id":"\d+"})
     */
    public function delete(Request $request, Trick $trick, ImageRepository $imageRepository): Response
    {
        // check delete voter
        $this->denyAccessUnlessGranted('trick_delete', $trick);

        if ($this->isCsrfTokenValid('delete' . $trick->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            // Delete all images linked to the trick
            $images = $imageRepository->findBy(['trick' => $trick]);
            foreach ($images as $image) {
                // the images in the ina_array are the images that we should'nt delete, they are used in the fixture
                if ($image->getPath() && !in_array($image->getPath(), ['snowboard_main.jpeg', '180.jpeg', '360.jpeg', '540.jpeg', '1080.jpeg', 'tailSlide.jpeg', 'japan.jpeg', 'nosegrab.jpeg', 'mactwist.jpeg', 'mute.jpeg', 'sad.jpeg', 'indy.jpeg'])) {
                    if (file_exists($this->getParameter('trickUpload_directory') . "/" . $image->getPath())) {
                        $fileToDelete = new File($this->getParameter('trickUpload_directory') . "/" . $image->getPath());
                        $this->deleteFile($fileToDelete);
                    }
                }
            }
            if ($trick->getMainImgName() && $trick->getMainImgName() !== "snowboard_main.jpeg") {
                if (file_exists($this->getParameter('trickUpload_directory') . "/" . $trick->getMainImgName())) {
                    $mainImgTodelete = new File($this->getParameter('trickUpload_directory') . "/" . $trick->getMainImgName());
                    $this->deleteFile($mainImgTodelete);
                }
            }

            $em->remove($trick);
            $em->flush();
        }

        return $this->redirectToRoute('home');
    }

    private function deleteFile($fileToDelete)
    {
        if (file_exists($fileToDelete->getPathname())) {
            unlink($fileToDelete);
        }
    }

    /**
     * @Route("/getMoreComments", name="getMoreComments", methods={"POST"})
     */
    public function getMoreComments(Request $request, CommentRepository $commentRepository)
    {
        $offset = $request->request->get('offset');
        $newComments = $commentRepository->findBy([], null, 5, $offset);

        return $this->json(
            [
                'newComments' => $newComments
            ],
            200,
            [],
            ['groups' => 'comment']
        );
    }
}
