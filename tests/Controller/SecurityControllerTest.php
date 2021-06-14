<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Tests\NeedLoginTrait;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class SecurityControllerTest extends WebTestCase
{
    use NeedLoginTrait;

    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    public function testDisplayLogin()
    {
        $this->client->request('GET', '/login');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('h1', 'Se connecter');
        $this->assertSelectorNotExists('.alert.alert-danger');
    }

    public function testLoginWithBadCredentials()
    {
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => 'demo',
            '_password' => 'fakepassword'
        ]);
        $this->client->submit($form);
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert.alert-danger');
    }

    public function testSuccessfullLogin()
    {
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => 'demo',
            '_password' => '1234Jean%1234'
        ]);
        $this->client->submit($form);
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertSelectorNotExists('.alert.alert-danger');
    }

    public function testDisplaySubscribe()
    {
        $this->client->request('GET', '/subscription');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('h1', 'Inscription');
    }

    public function testDisplayForgottenPassword()
    {
        $this->client->request('GET', '/forgottenPassword');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('h1', 'Mot de passe oublié');
    }

    public function testResetPasswordBadToken()
    {
        $this->client->request('GET', '/resetPassword/badToken');
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testGoodSubscription()
    {
        $crawler = $this->client->request('GET', '/subscription');
        $form = $crawler->selectButton("S'inscrire")->form([
            'user[username]' => 'demo3',
            'user[mail]' => 'demo3@hotmail.fr',
            'user[password][first]' => '1234Jean%1234',
            'user[password][second]' => '1234Jean%1234'
        ]);
        $this->client->submit($form);
        $this->assertResponseRedirects();
    }

    public function testSendMailSubscription()
    {
        $crawler = $this->client->request('GET', '/subscription');
        $form = $crawler->selectButton("S'inscrire")->form([
            'user[username]' => 'demo4',
            'user[mail]' => 'demo4@hotmail.fr',
            'user[password][first]' => '1234Jean%1234',
            'user[password][second]' => '1234Jean%1234'
        ]);
        // enableProfiler works only for one Request, we need to reuse it for each request if needed : https://symfony.com/doc/current/testing/profiling.html
        $this->client->enableProfiler();
        $this->client->submit($form);
        $mailCollector = $this->client->getProfile()->getCollector('mailer');
        $this->assertEquals(1, count($mailCollector->getEvents()->getEvents()));
    }

    public function testValidateEmail(){
        $crawler = $this->client->request('GET', '/subscription');
        $form = $crawler->selectButton("S'inscrire")->form([
            'user[username]' => 'demo3',
            'user[mail]' => 'demo3@hotmail.fr',
            'user[password][first]' => '1234Jean%1234',
            'user[password][second]' => '1234Jean%1234'
        ]);
        $this->client->submit($form);

        $userSubscriptionToken = $this->em->getRepository(User::class)->findOneBy(["username" => "demo3"])->getSubscriptionToken();
        $this->client->request('GET', '/validateEmail/'.$userSubscriptionToken);
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testValidateEmailWrongToken(){
        $this->client->request('GET', '/validateEmail/blabla');
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testForgottenPasswordValidForm()
    {
        $crawler = $this->client->request('GET', '/forgottenPassword');
        $form = $crawler->selectButton("Envoyer un mail")->form([
            'forgotten_password[mail]' => 'demo2@hotmail.fr',
        ]);
        $this->client->enableProfiler();
        $this->client->submit($form);
        $mailCollector = $this->client->getProfile()->getCollector('mailer');
        $this->assertEquals(1, count($mailCollector->getEvents()->getEvents()));
    }

    public function testForgottenPasswordWrongForm()
    {
        $crawler = $this->client->request('GET', '/forgottenPassword');
        $form = $crawler->selectButton("Envoyer un mail")->form([
            'forgotten_password[mail]' => 'demo3',
        ]);
        $this->client->submit($form);
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertSelectorNotExists('.alert.alert-danger');
    }

    public function testResetPassword(){
        // needed to change the passwordToken of demo2
        $crawler = $this->client->request('GET', '/forgottenPassword');
        $form = $crawler->selectButton("Envoyer un mail")->form([
            'forgotten_password[mail]' => 'demo2@hotmail.fr',
        ]);
        $this->client->submit($form);

        $userChangePasswordToken = $this->em->getRepository(User::class)->findOneBy(["username" => "demo2"])->getChangePasswordToken();
        $crawler = $this->client->request('GET', '/resetPassword/'.$userChangePasswordToken);
        $form = $crawler->selectButton("Changer le mot de passe")->form([
            'reset_password[password][first]' => '1234Jean%1234',
            'reset_password[password][second]' => '1234Jean%1234'
        ]);
        $this->client->submit($form);
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testResetPasswordWrongToken(){
        $this->client->request('GET', '/resetPassword/blabla');
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
}
