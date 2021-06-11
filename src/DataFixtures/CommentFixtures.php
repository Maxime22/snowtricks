<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use App\DataFixtures\UserFixtures;
use App\DataFixtures\TrickFixtures;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class CommentFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $author = UserFixtures::DEMO_USER_REFERENCE;
        $trick = TrickFixtures::DEMO_TRICK_REFERENCE;

        for ($i = 0; $i < 20; $i++) {
            $comment = new Comment();
            $comment->setTitle('Comment'.$i);
            $comment->setAuthor($this->getReference($author));
            $comment->setTrick($this->getReference($trick));
            $comment->setContent('HelloCommentBlabla '. $i);
            $comment->setCreatedAt();
            $manager->persist($comment);
        }

        $manager->flush();
    }

    // otherwise, by default, fixtures are linked in the alphabetical order and comment couldn't have user demo const
    public function getDependencies()
    {
        return [
            UserFixtures::class,
            TrickFixtures::class,
        ];
    }
}
