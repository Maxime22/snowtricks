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
    public function new(Request $request, FileUploader $fileUploader, UserRepository $userRepository): Response
    {
        $trick = new Trick();

        $author = $userRepository->find($this->getUser());
        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->persistValidForm($form, $fileUploader, $trick, $author);
            return $this->redirectToRoute('home');
        }

        return $this->render('trick/new.html.twig', [
            'trick' => $trick,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/trick/{slug}/{id}", name="trick_show", methods={"GET","POST"}, requirements={"id":"\d+","slug":"[a-z0-9\-]*"})
     */
    public function show(Trick $trick, Request $request, CommentRepository $commentRepository): Response
    {
        $em = $this->getDoctrine()->getManager();
        $comments = $commentRepository->findBy(['trick' => $trick]);

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
    public function edit(Request $request, Trick $trick, FileUploader $fileUploader, ImageRepository $imageRepository): Response
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
            $this->persistValidForm($form, $fileUploader, $trick, null, $arrayPhotoNames);
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
                $fileToDelete = new File($this->getParameter('trickUpload_directory') . "/" . $image->getPath());
                $this->deleteFile($fileToDelete);
            }
            if ($trick->getMainImgName()) {
                $mainImgTodelete = new File($this->getParameter('trickUpload_directory') . "/" . $trick->getMainImgName());
                $this->deleteFile($mainImgTodelete);
            }

            $em->remove($trick);
            $em->flush();
        }

        return $this->redirectToRoute('home');
    }

    private function persistValidForm($form, $fileUploader, $trick, $author = null, $oldImages = null)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var UploadedFile $mainImgFile */
        $mainImgFile = $form->get('mainImg')->getData();

        $videos = $form->get('videos')->getData();

        if ($mainImgFile) {
            $oldFile = null;
            if ($trick->getMainImgName()) {
                $oldFile = new File($this->getParameter('trickUpload_directory') . "/" . $trick->getMainImgName());
            }
            $newFilename = $fileUploader->upload($mainImgFile, $this->getParameter('trickUpload_directory'), $oldFile);
            $trick->setMainImgName($newFilename);
        } else if (!$trick->getMainImgName()) {
            $trick->setMainImgName('snowboard_main.jpeg');
        }

        // update images
        $images = $trick->getImages();
        $newImages = [];
        if ($images) {
            foreach ($images as $image) {
                if ($image->getFile() || $image->getPath()) {
                    if ($image->getFile() !== null) {
                        $newFilename = $fileUploader->upload($image->getFile(), $this->getParameter('trickUpload_directory'));
                        $newImages[] = $newFilename;
                    }
                    if ($image->getFile() === null) {
                        $newFilename = $image->getPath();
                        $newImages[] = $newFilename;
                    }
                    $image->setPath($newFilename);
                    $image->setTrick($trick);
                }
            }
        }

        // Delete image files
        if ($oldImages) {
            $imagesToDelete = array_diff($oldImages, $newImages);
            foreach ($imagesToDelete as $imageToDelete) {
                $fileToDelete = new File($this->getParameter('trickUpload_directory') . "/" . $imageToDelete);
                $this->deleteFile($fileToDelete);
            }
        }

        // author is null in the edit
        if ($author) {
            $trick->setAuthor($author);
        }
        $trick->setVideos($videos);
        $trick->setCreatedAt();

        $em->persist($trick);
        $em->flush();
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
