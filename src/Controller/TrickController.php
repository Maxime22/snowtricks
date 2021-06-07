<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Image;
use App\Entity\Trick;
use App\Entity\Comment;
use App\Form\TrickType;
use App\Form\CommentType;
use App\Service\FileUploader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class TrickController extends BaseController
{
    /**
     * @Route("/trick/new", name="trick_new", methods={"GET","POST"})
     */
    public function new(Request $request, FileUploader $fileUploader): Response
    {
        $trick = new Trick();

        // TODO : change the user when the login is done
        $author = $this->em->getRepository(User::class)->find($this->security->getUser());
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
    public function show(Trick $trick, Request $request): Response
    {
        $comments = $this->em->getRepository(Comment::class)->findBy(['trick'=>$trick]);

        $newComment = new Comment();
        $formComment = $this->createForm(CommentType::class, $newComment);
        $formComment->handleRequest($request);
        if ($formComment->isSubmitted() && $formComment->isValid()) {
            $newComment->setAuthor($this->security->getUser());
            $newComment->setCreatedAt();
            $newComment->setTrick($trick);
            $this->em->persist($newComment);
            $this->em->flush();
            return $this->redirectToRoute('trick_show',['id'=>$trick->getId(),'slug'=>$trick->getSlug()]);
        }

        return $this->render('trick/show.html.twig', [
            'trick' => $trick,
            'formComment' => $formComment->createView(),
            'comments' => $comments 
        ]);
    }

    /**
     * @Route("/trick/{id}/edit", name="trick_edit", methods={"GET","POST"}, requirements={"id":"\d+"})
     */
    public function edit(Request $request, Trick $trick, FileUploader $fileUploader): Response
    {
        $images = $this->em->getRepository(Image::class)->findBy(['trick' => $trick]);
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
     * @Route("/trick/{id}", name="trick_delete", methods={"POST"})
     */
    public function delete(Request $request, Trick $trick): Response
    {
        if ($this->isCsrfTokenValid('delete' . $trick->getId(), $request->request->get('_token'))) {
            // Delete all images linked to the trick
            $images = $this->em->getRepository(Image::class)->findBy(['trick' => $trick]);
            foreach ($images as $image) {
                $fileToDelete = new File($this->getParameter('trickUpload_directory') . "/" . $image->getPath());
                $this->deleteFile($fileToDelete);
            }
            if ($trick->getMainImgName()) {
                $mainImgTodelete = new File($this->getParameter('trickUpload_directory') . "/" . $trick->getMainImgName());
                $this->deleteFile($mainImgTodelete);
            }

            $this->em->remove($trick);
            $this->em->flush();
        }

        return $this->redirectToRoute('home');
    }

    private function persistValidForm($form, $fileUploader, $trick, $author = null, $oldImages = null)
    {

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
        }else if (!$trick->getMainImgName()){
            $trick->setMainImgName('snowboard_main.jpeg');
        }

        // update images
        $images = $trick->getImages();
        $newImages = [];
        if ($images) {
            foreach ($images as $image) {
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

        $this->em->persist($trick);
        $this->em->flush();
    }

    private function deleteFile($fileToDelete)
    {
        if (file_exists($fileToDelete->getPathname())) {
            unlink($fileToDelete);
        }
    }
}
