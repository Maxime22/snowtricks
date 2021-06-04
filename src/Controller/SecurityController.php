<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class SecurityController extends BaseController
{

    /**
     * @Route("/login", name="login")
     */
    public function login(AuthenticationUtils $authenticationUtils)
    {
        if ($this->security->getUser()) {
            return $this->redirectToRoute('home');
        }
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        return $this->render('security/login.html.twig', ["lastUsername" => $lastUsername, "error" => $error]);
    }

    /**
     * @Route("/subscription", name="subscription")
     */
    public function subscription(
        Request $request,
        UserPasswordEncoderInterface $encoder,
        \Swift_Mailer $mailer,
        TokenGeneratorInterface $tokenGenerator
    ) {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($password);
            $user->setCreatedAt();
            $user->setIsValidated(false);

            // we send a mail to obligate to validate the email
            $token = $tokenGenerator->generateToken();
            $user->setSubscriptionToken($token);

            // we create the url sent to the user
            $url = $this->generateUrl('validateEmail', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);
            $message = (new \Swift_Message('Validation de votre mail'))
                ->setFrom('2biolibre@gmail.com')
                ->setTo($user->getMail())
                ->setBody(
                    "Votre url de validation : " . " " . $url,
                    'text/html'
                );

            // this line counts the number of mail sent
            $mailNumber = $mailer->send($message);
            
            if ($mailNumber > 0) {
                $this->addFlash('notice', 'Un mail de validation vous a été envoyé');
                $this->em->persist($user);
                $this->em->flush();
            } else {
                $this->addFlash('danger', 'Problème lors de l\'envoi du mail');
            }

            return $this->redirectToRoute('login');
        }

        return $this->render(
            'security/subscription.html.twig',
            array('form' => $form->createView())
        );
    }


    /**
     * @Route("/validateEmail/{token}", name="validateEmail")
     */
    public function validateEmail(string $token): Response
    {
        // we find the user
        $user = $this->em->getRepository(User::class)->findOneBy(['subscriptionToken' => $token]);

        if ($user === null) {
            return $this->redirectToRoute('login');
        } else {
            // we active the account
            $user->setIsValidated(true);
            $user->setSubscriptionToken(null);

            $this->em->persist($user);
            $this->em->flush();

            $this->addFlash('notice', 'Inscription validée');
            return $this->redirectToRoute('login');
        }

        return $this->render('security/validateEmail.html.twig', ['token' => $token]);
    }
}
