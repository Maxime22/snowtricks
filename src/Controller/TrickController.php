<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Image;
use App\Entity\Trick;
use App\Form\TrickType;
use App\Service\FileUploader;
use App\Repository\TrickRepository;
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
        $author = $this->em->getRepository(User::class)->find(1);
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
     * @Route("/trick/{slug}/{id}", name="trick_show", methods={"GET"}, requirements={"id":"\d+"})
     */
    public function show(Trick $trick): Response
    {
        return $this->render('trick/show.html.twig', [
            'trick' => $trick,
        ]);
    }

    /**
     * @Route("/trick/{id}/edit", name="trick_edit", methods={"GET","POST"}, requirements={"id":"\d+"})
     */
    public function edit(Request $request, Trick $trick, FileUploader $fileUploader): Response
    {
        // TODO : change the user when the login is done
        $author = $this->em->getRepository(User::class)->find(1);

        $images = $this->em->getRepository(Image::class)->findBy(['trick' => $trick]);
        $arrayPhotoNames = null;
        foreach ($images as $image) {
            $arrayPhotoNames[] = $image->getPath();
            $trick->addImage($image);
        }

        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->persistValidForm($form, $fileUploader, $trick, $author, $arrayPhotoNames);
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

    private function persistValidForm($form, $fileUploader, $trick, $author, $oldImages = null)
    {

        /** @var UploadedFile $mainImgFile */
        $mainImgFile = $form->get('mainImg')->getData();

        $videos = $form->get('videos')->getData();

        if ($mainImgFile) {
            $oldFile = null;
            // delete the old img, WORKS, TODO : we need to uncomment after test in edit
            if ($trick->getMainImgName()) {
                $oldFile = new File($this->getParameter('trickUpload_directory') . "/" . $trick->getMainImgName());
            }
            $newFilename = $fileUploader->upload($mainImgFile, $this->getParameter('trickUpload_directory'), $oldFile);
            $trick->setMainImgName($newFilename);
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

        // TODO videos

        $trick->setAuthor($author);
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
