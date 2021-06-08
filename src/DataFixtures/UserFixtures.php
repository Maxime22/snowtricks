<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public const DEMO_USER_REFERENCE = 'demo-user';

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setUsername('demo');
        $user->setMail('max092@hotmail.fr');
        $user->setCreatedAt();
        $user->setIsValidated(true);
        $user->setPassword($this->passwordHasher->hashPassword($user,'demo'));
        $manager->persist($user);
        $manager->flush();

        // other fixtures can get this object using the UserFixtures::ADMIN_USER_REFERENCE constant
        $this->addReference(self::DEMO_USER_REFERENCE, $user);
    }
}
