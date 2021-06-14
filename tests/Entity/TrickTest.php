<?php

namespace App\Tests\Entity;

use App\Entity\Trick;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TrickTest extends KernelTestCase
{
    public function getValidEntity(): Trick
    {
        return (new Trick())
            ->setTitle("Hello")
            ->setContent("Blablablablabla");
    }

    public function assertHasErrors(Trick $trick, int $errorNumber = 0)
    {
        self::bootKernel();
        $errors = self::getContainer()->get('debug.validator')->validate($trick);
        $messages = [];
        /** @var ConstraintViolation $error */
        foreach ($errors as $error) {
            $messages[]= $error->getPropertyPath(). ' => ' . $error->getMessage();
        }
        $this->assertCount($errorNumber, $errors, implode(', ', $messages));
    }

    /* VALIDATORS */
    public function testValidTrick()
    {
        $trick = $this->getValidEntity();
        $this->assertHasErrors($trick);
    }

    public function testShortTitleTrick()
    {
        $trick = $this->getValidEntity()->setTitle("He");
        $this->assertHasErrors($trick, 1);
    }

    public function testShortContentTrick()
    {
        $trick = $this->getValidEntity()->setContent("Blabla");
        $this->assertHasErrors($trick, 1);
    }

    public function testInvalidBlankTitleTrick()
    {
        $trick = $this->getValidEntity()->setTitle("");
        $this->assertHasErrors($trick, 1);
    }

    public function testInvalidBlankContentTrick()
    {
        $trick = $this->getValidEntity()->setContent("");
        $this->assertHasErrors($trick, 1);
    }

    // test the unicity of the slug
    public function testInvalidUsedSlugTrick(){
        $trick = $this->getValidEntity()->setSlug("le-180");
        $this->assertHasErrors($trick, 1);
    }
}
