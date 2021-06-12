<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Tests\NeedLoginTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class UserControllerTest extends WebTestCase{

    use NeedLoginTrait;

    public function setUp(): void
    {
        $this->client = static::createClient();

        $this->em = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    public function testProfileIsRedirected(){
        $this->client->request('GET', '/profile/1');
        $this->assertResponseRedirects();
    }

    public function testProfileIsWorking(){
        $user = $this->em->getRepository(User::class)->findOneBy(["username" => "demo"]);
        $this->login($this->client, $user);
        $crawler = $this->client->request('GET', '/profile/1');
        $this->assertSelectorTextContains('h1', 'Mon profil');
    }

    public function testProfileIsNotWorkingForWrongUser(){
        $user = $this->em->getRepository(User::class)->findOneBy(["username" => "demo2"]);
        $this->login($this->client, $user);
        $crawler = $this->client->request('GET', '/profile/1');
        $this->assertResponseRedirects();
    }

    public function testProfileUpdate(){
        $user = $this->em->getRepository(User::class)->findOneBy(["username" => "demo"]);
        $this->login($this->client, $user);
        $crawler = $this->client->request('GET', '/profile/1');
        $form = $crawler->selectButton('Sauvegarder')->form([
            'profile[mail]' => 'demo@hotmail.fr',
            'profile[username]' => 'demo'
        ]);
        
        $this->client->submit($form);
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert.alert-primary');
    }

    // TODO tester l'envoi d'un formulaire dans la page profile (j'ai eu un bug à cause d'un mdp)

}