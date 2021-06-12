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
        $data=[
            "username"=>['demo','demo2'],
            "mail"=>['demo@hotmail.fr', 'demo2@hotmail.fr']
        ];

        for ($i = 0; $i < count($data['username']); $i++) {
            $user = new User();
            $user->setUsername($data['username'][$i]);
            $user->setMail($data['mail'][$i]);
            $user->setCreatedAt();
            $user->setIsValidated(true);
            $user->setPassword($this->passwordHasher->hashPassword($user, "1234Jean%1234"));
            $manager->persist($user);
        }

        $manager->flush();

        // other fixtures can get this object using the UserFixtures::ADMIN_USER_REFERENCE constant
        $this->addReference(self::DEMO_USER_REFERENCE, $user);
    }
}
