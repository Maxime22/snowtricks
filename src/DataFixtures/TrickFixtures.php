<?php

namespace App\DataFixtures;

use App\Entity\Trick;
use App\DataFixtures\UserFixtures;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class TrickFixtures extends Fixture implements DependentFixtureInterface
{
    public const DEMO_TRICK_REFERENCE = 'demo-trick';

    public function load(ObjectManager $manager)
    {
        $author = UserFixtures::DEMO_USER_REFERENCE;

        for ($i = 0; $i < 20; $i++) {
            $trick = new Trick();
            $trick->setMainImgName('snowboard_main.jpeg');
            $trick->setTitle('Figure'.$i);
            $trick->setTrickGroup('grab');
            $trick->setContent('Hello '. $i);
            $trick->setAuthor($this->getReference($author));
            $trick->setCreatedAt();
            $manager->persist($trick);
        }

        $manager->flush();

        $this->addReference(self::DEMO_TRICK_REFERENCE, $trick);
    }

    // otherwise, by default, fixtures are linked in the alphabetical order and trick couldn't have user demo const
    public function getDependencies()
    {
        return [
            UserFixtures::class,
        ];
    }
}
