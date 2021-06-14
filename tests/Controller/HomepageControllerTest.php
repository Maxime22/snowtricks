<?php

namespace App\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class HomepageControllerTest extends WebTestCase
{
    public function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testHomepage()
    {
        $this->client->request("GET", "/");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testHomePageTitle(){
        $this->client->request("GET", "/");
        $this->assertPageTitleSame("SnowTricks");
    }

    public function testH1Homepage()
    {
        $crawler = $this->client->request("GET", "/");
        $this->assertCount(1, $crawler->filter('h1'));
    }

    public function testConnectLinkHomepage()
    {
        $crawler = $this->client->request("GET", "/");
        $link = $crawler->selectLink('Se connecter')->link();
        $this->client->click($link);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('h1', 'Se connecter');
    }

    public function testHomeLinkHomepage()
    {
        $crawler = $this->client->request("GET", "/");
        $link = $crawler->selectLink('Accueil')->link();
        $this->client->click($link);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testHomePageHas10Tricks(){
        $crawler = $this->client->request("GET", "/");
        $this->assertCount(10, $crawler->filter('.trickHomeImg'));
    }

    public function testGoDownButtonExistsHomePage(){
        $crawler = $this->client->request("GET", "/");
        $this->assertCount(1, $crawler->filter('#goDown'));
    }
}
