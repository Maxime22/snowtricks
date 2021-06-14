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

        $data=[
            "author"=>$author,
            "main_img_name"=>['180.jpeg','360.jpeg', '540.jpeg', '1080.jpeg', 'tailSlide.jpeg', 'japan.jpeg', 'nosegrab.jpeg', 'mactwist.jpeg', 'mute.jpeg', 'sad.jpeg', 'indy.jpeg'],
            "title"=>['Le 180','Le 360', 'Le 540', 'Le 1080', 'Le tail slide', 'Le japan', 'Le nose grab', 'Le Mac Twist', ' Le mute', 'Le sad', 'Le indy'],
            "trick_group"=>['rotation','rotation','rotation','rotation','slide','grab', 'grab', 'flip', 'grab', 'grab', 'grab'],
            "content"=>["blablablablablabla"],
        ];

        for ($i = 0; $i < count($data['main_img_name']); $i++) {
            $trick = new Trick();
            
            $trick->setMainImgName($data['main_img_name'][$i]);
            $trick->setTitle($data['title'][$i]);
            $trick->setTrickGroup($data['trick_group'][$i]);
            $trick->setContent($data['content'][0]);
            $trick->setAuthor($this->getReference($author));
            $trick->setCreatedAt();
            if($i===0){
                $trick->setVideos(["https://www.youtube.com/embed/WHLu1rSMEvQ"]);
                $this->addReference(self::DEMO_TRICK_REFERENCE, $trick);
            }
            $manager->persist($trick);
        }

        $manager->flush();
        
    }

    // otherwise, by default, fixtures are linked in the alphabetical order and trick couldn't have user demo const
    public function getDependencies()
    {
        return [
            UserFixtures::class,
        ];
    }
}
