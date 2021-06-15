<?php

namespace App\Service;

use App\Entity\Trick;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CheckAndPersistTrickForm
{
    private $em;
    private $fileUploader;
    private $container;

    public function __construct(EntityManager $em, FileUploader $fileUploader, ContainerInterface $container)
    {
        $this->em = $em;
        $this->fileUploader = $fileUploader;
        $this->container = $container;
    }

    public function persistValidForm(Request $request, FormInterface $form, Trick $trick, $datas)
    {
        /** @var UploadedFile $mainImgFile */
        $mainImgFile = $form->get('mainImg')->getData();

        $mainImgSrcData = $request->request->get('mainImgSrcData');

        $videos = $form->get('videos')->getData();

        if ($mainImgFile) {
            $oldFileMainImg = $this->checkMainImgAndReturnOldFile($trick);
            $newFilename = $this->fileUploader->upload($mainImgFile, $this->container->getParameter('trickUpload_directory'), $oldFileMainImg);
            $trick->setMainImgName($newFilename);
        } else if (!$trick->getMainImgName() || $mainImgSrcData === "1") {
            $trick->setMainImgName('snowboard_main.jpeg');
        }

        // update images
        $newImages = $this->updateImages($trick);

        $author = $datas['author'];
        $oldImages = $datas['arrayPhotoNames'];
        // Delete image files
        if ($oldImages) {
            $this->deleteOldImageFiles($oldImages, $newImages);
        }

        $this->setAndPersist($author, $trick, $videos);
    }

    private function setAndPersist($author, Trick $trick, $videos)
    {
        // author is null in the edit
        if ($author) {
            $trick->setAuthor($author);
        }
        $trick->setVideos($videos);
        $trick->setCreatedAt();

        $this->em->persist($trick);
        $this->em->flush();
    }

    private function updateImages(Trick $trick)
    {
        $images = $trick->getImages();
        $newImages = [];
        if ($images) {
            foreach ($images as $image) {
                if ($image->getFile() || $image->getPath()) {
                    $newImages = $this->updateNewImages($image, $newImages);
                    $image->setTrick($trick);
                }
            }
        }
        return $newImages;
    }

    private function updateNewImages($image, $newImages)
    {
        if ($image->getFile() !== null) {
            $newFilename = $this->fileUploader->upload($image->getFile(), $this->container->getParameter('trickUpload_directory'));
        } else {
            $newFilename = $image->getPath();
        }
        $newImages[] = $newFilename;
        $image->setPath($newFilename);
        return $newImages;
    }

    private function deleteOldImageFiles($oldImages, $newImages)
    {
        $imagesToDelete = array_diff($oldImages, $newImages);
        foreach ($imagesToDelete as $imageToDelete) {
            if (file_exists($this->container->getParameter('trickUpload_directory') . "/" . $imageToDelete)) {
                $fileToDelete = new File($this->container->getParameter('trickUpload_directory') . "/" . $imageToDelete);
                $this->deleteFile($fileToDelete);
            }
        }
    }

    private function deleteFile($fileToDelete)
    {
        if (file_exists($fileToDelete->getPathname())) {
            unlink($fileToDelete);
        }
    }

    private function checkMainImgAndReturnOldFile($trick)
    {
        $oldFile = null;
        if ($trick->getMainImgName() && $trick->getMainImgName() !== "snowboard_main.jpeg") {
            if (file_exists($this->container->getParameter('trickUpload_directory') . "/" . $trick->getMainImgName())) {
                $oldFile = new File($this->container->getParameter('trickUpload_directory') . "/" . $trick->getMainImgName());
            }
        }
        return $oldFile;
    }
}
