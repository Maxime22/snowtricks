<?php

namespace App\Tests\Entity;

use App\Entity\Image;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\File\File;

class ImageTest extends KernelTestCase
{
    public function getValidEntity(): Image
    {
        $file = new File (self::getContainer()->getParameter('testImages_directory').'/snowboard_main.jpeg');
        return (new Image())
            ->setFile($file);
    }

    public function getTooBigEntity(): Image
    {
        $file = new File (self::getContainer()->getParameter('testImages_directory').'/snowboard_main_too_big.jpeg');
        return (new Image())
            ->setFile($file);
    }

    public function assertHasErrors(Image $image, int $errorNumber = 0)
    {
        self::bootKernel();
        $errors = self::getContainer()->get('debug.validator')->validate($image);
        $messages = [];
        /** @var ConstraintViolation $error */
        foreach ($errors as $error) {
            $messages[]= $error->getPropertyPath(). ' => ' . $error->getMessage();
        }
        $this->assertCount($errorNumber, $errors, implode(', ', $messages));
    }

    /* VALIDATORS */
    public function testValidImage()
    {
        $image = $this->getValidEntity();
        $this->assertHasErrors($image, 0);
    }

    public function testTooBigImage()
    {
        $image = $this->getTooBigEntity();
        $this->assertHasErrors($image, 1);
    }

    public function testInvalidMimeTypeImage()
    {
        $file = new File (self::getContainer()->getParameter('testImages_directory').'/sample.pdf');
        $image = $this->getValidEntity()->setFile($file);
        $this->assertHasErrors($image, 1);
    } 


}