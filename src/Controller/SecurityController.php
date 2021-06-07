<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Form\ResetPasswordType;
use Symfony\Component\Mime\Email;
use App\Repository\UserRepository;
use App\Form\ForgottenPasswordType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class SecurityController extends AbstractController
{

    /**
     * @Route("/login", name="login")
     */
    public function login(AuthenticationUtils $authenticationUtils)
    {
        if ($this->getUser()) {
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
        MailerInterface $mailer,
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
            $url = $this->generateUrl('validateEmail', ['subscriptionToken' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

            try {
                $em = $this->getDoctrine()->getManager();
                $this->sendMessage($mailer, $user, 'Validation de votre mail', "Votre url de validation : " . " " . $url);
                $this->addFlash('notice', 'Un mail de validation vous a été envoyé');
                $em->persist($user);
                $em->flush();
            } catch (TransportExceptionInterface $e) {
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
     * @Route("/validateEmail/{subscriptionToken}", name="validateEmail")
     */
    public function validateEmail(User $user): Response
    {
        $em = $this->getDoctrine()->getManager();

        $redirectNullUser = $this->checkUser($user, '', 'login');
        if ($redirectNullUser) return $redirectNullUser;

        // we active the account
        $user->setIsValidated(true);
        $user->setSubscriptionToken(null);
        $user->setUpdatedAt();

        $em->persist($user);
        $em->flush();

        $this->addFlash('notice', 'Inscription validée');
        return $this->redirectToRoute('login');
    }

    /**
     * @Route("/forgottenPassword", name="forgotten_password")
     */
    public function forgottenPassword(Request $request, TokenGeneratorInterface $tokenGenerator, MailerInterface $mailer, UserRepository $userRepository): Response
    {
        $form = $this->createForm(ForgottenPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $email = $form->get('mail')->getData();
            $user = $userRepository->findOneBy(['mail' => $email]);

            $redirectNullUser = $this->checkUser($user, 'Mail envoyé', 'forgotten_password');
            if ($redirectNullUser) return $redirectNullUser;

            $token = $tokenGenerator->generateToken();

            $user->setChangePasswordToken($token);
            $user->setUpdatedAt();

            // we create the url sent to the user
            $url = $this->generateUrl('reset_password', array('changePasswordToken' => $token), UrlGeneratorInterface::ABSOLUTE_URL);

            try {
                $this->sendMessage($mailer, $user, 'Changement de mot de passe', "Votre url de changement de mot de passe : " . " " . $url);
                $this->addFlash('notice', 'Mail envoyé');
                $em->flush();
            } catch (TransportExceptionInterface $e) {
                $this->addFlash('danger', 'Problème lors de l\'envoi du mail');
            }

            return $this->redirectToRoute('forgotten_password');
        }

        return $this->render('security/forgottenPassword.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/reset_password/{changePasswordToken}", name="reset_password")
     */
    public function resetPassword(User $user, Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $redirectNullUser = $this->checkUser($user, 'Token Unknown', 'login');
            if ($redirectNullUser) return $redirectNullUser;

            $user->setPassword($passwordEncoder->encodePassword($user, $form->get('password')->getData()));
            $user->setChangePasswordToken(null);
            $user->setUpdatedAt();
            $em->flush();
            $this->addFlash('notice', 'Mot de passe modifié');
            return $this->redirectToRoute('login');
        }

        return $this->render('security/resetPassword.html.twig', ['token' => $user->getChangePasswordToken(), 'form' => $form->createView()]);
    }

    private function sendMessage($mailer, $user, $titleMail, $messageMail)
    {
        $email = (new Email())
            ->from('2biolibre@gmail.com')
            ->to($user->getMail())
            ->subject($titleMail)
            ->text($messageMail);

        $mailer->send($email);
    }

    private function checkUser($user, $flashMessage, $routeToRedirect)
    {
        if ($user === null) {
            // We say that all is ok but the user doesn't really exists, we don't want someone know there the email doesn't exist
            $this->addFlash('notice', $flashMessage);
            return $this->redirectToRoute($routeToRedirect);
        }
        return null;
    }
}
