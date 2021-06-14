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

        $data = [
            "author" => $author,
            "main_img_name" => ['180.jpeg', '360.jpeg', '540.jpeg', '1080.jpeg', 'tailSlide.jpeg', 'japan.jpeg', 'nosegrab.jpeg', 'mactwist.jpeg', 'mute.jpeg', 'sad.jpeg', 'indy.jpeg'],
            "title" => ['Le 180', 'Le 360', 'Le 540', 'Le 1080', 'Le tail slide', 'Le japan', 'Le nose grab', 'Le Mac Twist', ' Le mute', 'Le sad', 'Le indy'],
            "trick_group" => ['rotation', 'rotation', 'rotation', 'rotation', 'slide', 'grab', 'grab', 'flip', 'grab', 'grab', 'grab'],
            "content" => [
                "Pour les néophytes, le backside 180 ou 180 back est un saut avec un demi tour qui s'effectue côté pointes de pieds en envoyant les épaules dos à la pente lors de la rotation, ce qui fait qu'à l’atterrissage on se retrouve en marche arrière. Comme dans toute rotation l’important est la synchronisation entre l’impulsion et la rotation des épaules.",
                "Le 3.6 front ou frontside 3 est un tricks intéressant car on peut y mettre facilement beaucoup de style. C’est une rotation de 360 degrés du côté frontside ( à gauche pour les regular et à droite pour les goofy). Comme le 3.6 back, la vitesse de rotation est assez facile à gérer, mais si l’impulsion parait plus évidente en lançant les épaules de face, l’atterrissage l'est beaucoup moins car on est de dos le dernier quart du saut. On appelle ça une reception blind side…",
                "Le 5.4 Front ou Frontside 540 est un saut ou l’on fait un tour et demi en l’air. Dans notre cas, ça se passe dans le sens frontside comme son nom l’indique, c’est à dire à droite pour les regular et à droite pour les goofies. On peut également l’exécuter en switch, c’est une Cab 5.4, les regulars tourneront alors à Droite et les goofies à gauche.",
                "Comme pour le 3.6 et le 5.4, on continue de tourner...",
                "On peut slider avec la planche centrée par rapport à la barre (celle-ci se situe approximativement au-dessous des pieds du rideur), mais aussi en nose slide, c'est-à-dire l'avant de la planche sur la barre, ou en tail slide, l'arrière de la planche sur la barre.",
                "Saisie de l'avant de la planche, avec la main avant, du côté de la carre frontside.",
                "Saisie de la partie avant de la planche, avec la main avant.",
                "Un flip est une rotation verticale. On distingue les front flips, rotations en avant, et les back flips, rotations en arrière.
                Il est possible de faire plusieurs flips à la suite, et d'ajouter un grab à la rotation.
                Les flips agrémentés d'une vrille existent aussi (Mac Twist, Hakon Flip...), mais de manière beaucoup plus rare, et se confondent souvent avec certaines rotations horizontales désaxées.",
                "Saisie de la carre frontside de la planche entre les deux pieds avec la main avant",
                "Le sad ou melancholie ou style week : saisie de la carre backside de la planche, entre les deux pieds, avec la main avant.",
                "Saisie de la carre frontside de la planche, entre les deux pieds, avec la main arrière."
            ],
        ];

        for ($i = 0; $i < count($data['main_img_name']); $i++) {
            $trick = new Trick();

            $trick->setMainImgName($data['main_img_name'][$i]);
            $trick->setTitle($data['title'][$i]);
            $trick->setTrickGroup($data['trick_group'][$i]);
            $trick->setContent($data['content'][$i]);
            $trick->setAuthor($this->getReference($author));
            $trick->setCreatedAt();
            if ($i === 0) {
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
