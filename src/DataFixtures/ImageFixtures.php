<?php

namespace App\DataFixtures;

use App\Entity\Image;
use App\DataFixtures\TrickFixtures;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ImageFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $trick = TrickFixtures::DEMO_TRICK_REFERENCE;

        $image = new Image();
        $image->setPath('snowboard_main.jpeg');
        $image->setTrick($this->getReference($trick));
        $manager->persist($image);

        $manager->flush();
    }

    // otherwise, by default, fixtures are linked in the alphabetical order and comment couldn't have user demo const
    public function getDependencies()
    {
        return [
            TrickFixtures::class,
        ];
    }
}
