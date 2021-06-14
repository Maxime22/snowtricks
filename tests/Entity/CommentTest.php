<?php

namespace App\Tests\Entity;

use App\Entity\Comment;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CommentTest extends KernelTestCase{

    public function getValidEntity(): Comment
    {
        return (new Comment())
            ->setTitle("Jean")
            ->setContent("Blablablabla")
            ;
    }

    public function assertHasErrors(Comment $comment, int $errorNumber = 0)
    {
        self::bootKernel();
        $errors = self::getContainer()->get('debug.validator')->validate($comment);
        $messages = [];
        /** @var ConstraintViolation $error */
        foreach ($errors as $error) {
            $messages[]= $error->getPropertyPath(). ' => ' . $error->getMessage();
        }
        $this->assertCount($errorNumber, $errors, implode(', ', $messages));
    }

    /* VALIDATORS */
    public function testValidComment()
    {
        $comment = $this->getValidEntity();
        $this->assertHasErrors($comment);
    }

    public function testShortTitleComment()
    {
        $user = $this->getValidEntity()->setTitle("Je");
        $this->assertHasErrors($user, 1);
    }

    public function testShortContentComment()
    {
        $user = $this->getValidEntity()->setContent("Je");
        $this->assertHasErrors($user, 1);
    }

}