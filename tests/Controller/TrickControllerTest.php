<?php

namespace App\Tests\Controller;

use App\Entity\Trick;
use App\Entity\User;
use App\Tests\NeedLoginTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TrickControllerTest extends WebTestCase
{
    use NeedLoginTrait;

    public function setUp(): void
    {
        $this->client = static::createClient();

        $this->em = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    public function testNewTrickIsRedirected()
    {
        $this->client->request('GET', '/trick/new');
        $this->assertResponseRedirects();
    }

    public function testEditTrickIsRedirected()
    {
        $this->client->request('GET', '/trick/1/edit');
        $this->assertResponseRedirects();
    }

    public function testDeleteTrickIsRedirected()
    {
        $this->client->request('GET', '/trick/1/delete');
        $this->assertResponseRedirects();
    }

    // TODO : change figure0 when fixture will be good for tricks
    public function testShowTrickIsWorking()
    {
        $this->client->request('GET', '/trick/figure0/1');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testLetAuthenticatedUserAccessNewTrick()
    {
        $user = $this->em->getRepository(User::class)->findOneBy(["username" => "demo"]);
        $this->login($this->client, $user);

        $this->client->request('GET', '/trick/new');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testLetAuthenticatedUserAccessEditTrick()
    {
        $user = $this->em->getRepository(User::class)->findOneBy(["username" => "demo"]);
        $this->login($this->client, $user);

        $this->client->request('GET', '/trick/1/edit');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testNoAccessDeleteTrickWithoutCSRF()
    {
        $user = $this->em->getRepository(User::class)->findOneBy(["username" => "demo2"]);
        $this->login($this->client, $user);

        $this->client->request('GET', '/trick/1/delete');
        // there is no csrf so we want a redirection
        $this->assertResponseRedirects();
    }

    // this test works but delete the first figure in the database, the other tests linked to the trick with the id 1 won't work after running it so i comment it
    /* public function testAccessDeleteTrickWithCSRF()
    {
        $user = $this->em->getRepository(User::class)->findOneBy(["username" => "demo"]);
        $this->login($this->client, $user);
        $crawler = $this->client->request('GET', '/trick/1/edit');
        // Get the form
        $form = $crawler->selectButton('Supprimer la figure')->form();
        $this->client->submit($form);
    } */

    public function testNoAccessDeleteTrickWrongUser()
    {
        $user = $this->em->getRepository(User::class)->findOneBy(["username" => "demo"]);
        $this->login($this->client, $user);
        $this->client->request('GET', '/trick/1/delete');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testTricks20Has5Comments(){
        $crawler = $this->client->request("GET", "/trick/figure19/20");
        $this->assertCount(5, $crawler->filter('.commentContainer'));
    }

    public function testShowAddComment(){
        $user = $this->em->getRepository(User::class)->findOneBy(["username" => "demo2"]);
        $this->login($this->client, $user);

        $crawler = $this->client->request("GET", "/trick/figure19/20");
        $form = $crawler->selectButton('Commenter')->form([
            'comment[title]' => 'demoComment',
            'comment[content]' => 'i am happy to add a comment youpi'
        ]);
        $this->client->submit($form);
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert.alert-success');
    }

    // TODO, tester le new et son formulaire, le edit et son formulaire => les images et les vidéos ? => faire le front en même temps (aussi pour le show)

    public function testNewTrick(){
        $user = $this->em->getRepository(User::class)->findOneBy(["username" => "demo"]);
        $this->login($this->client, $user);

        $crawler = $this->client->request("GET", "/trick/new");
        $form = $crawler->selectButton('Sauvegarder')->form([
            'trick[title]' => 'demoComment',
            'trick[content]' => 'i am happy to add a content youpi',
            'trick[trickGroup]' => 'slide'
        ]);
        $this->client->submit($form);
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertPageTitleSame("SnowTricks");

        $this->deleteTrick('demoComment');
    }

    public function testEditTrick(){
        $user = $this->em->getRepository(User::class)->findOneBy(["username" => "demo2"]);
        $this->login($this->client, $user);

        $crawler = $this->client->request("GET", "/trick/1/edit");
        // TODO change figure0 here
        $form = $crawler->selectButton('Sauvegarder')->form([
            'trick[title]' => 'Figure0',
            'trick[content]' => 'i am happy to add a content youpi',
            'trick[trickGroup]' => 'slide'
        ]);
        $this->client->submit($form);
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertPageTitleSame("SnowTricks");
    }

    public function deleteTrick($title){
        $trick = $this->em->getRepository(Trick::class)->findOneBy(["title" => $title]);
        $this->em->remove($trick);
        $this->em->flush();
    }
}
