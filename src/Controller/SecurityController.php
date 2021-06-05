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

            // this line counts the number of mail sent
            $mailNumber = $this->sendMessage($mailer, $user, 'Validation de votre mail', "Votre url de validation : " . " " . $url);

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

        $redirectNullUser = $this->checkUser($user, '', 'login');
        if($redirectNullUser) return $redirectNullUser;

        // we active the account
        $user->setIsValidated(true);
        $user->setSubscriptionToken(null);
        $user->setUpdatedAt();

        $this->em->persist($user);
        $this->em->flush();

        $this->addFlash('notice', 'Inscription validée');
        return $this->redirectToRoute('login');
    }

    /**
     * @Route("/forgottenPassword", name="forgotten_password")
     */
    public function forgottenPassword(Request $request, TokenGeneratorInterface $tokenGenerator, \Swift_Mailer $mailer): Response
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('mail');
            $user = $this->em->getRepository(User::class)->findOneBy(['mail' => $email]);

            $redirectNullUser = $this->checkUser($user, 'Mail Inconnu', 'forgotten_password');
            if($redirectNullUser) return $redirectNullUser;

            $token = $tokenGenerator->generateToken();

            try {
                $user->setChangePasswordToken($token);
                $user->setUpdatedAt();
                $this->em->flush();
            } catch (\Exception $e) {
                $this->addFlash('warning', $e->getMessage());
                return $this->redirectToRoute('forgotten_password');
            }

            // we create the url sent to the user
            $url = $this->generateUrl('reset_password', array('token' => $token), UrlGeneratorInterface::ABSOLUTE_URL);

            $mailNumber = $this->sendMessage($mailer, $user, 'Changement de mot de passe', "Votre url de changement de mot de passe : " . " " . $url);

            if ($mailNumber > 0) {
                $this->addFlash('notice', 'Mail envoyé');
            }

            return $this->redirectToRoute('forgotten_password');
        }

        return $this->render('security/forgottenPassword.html.twig');
    }

    /**
     * @Route("/reset_password/{token}", name="reset_password")
     */
    public function resetPassword(Request $request, string $token, UserPasswordEncoderInterface $passwordEncoder)
    {
        if ($request->isMethod('POST')) {
            $user = $this->em->getRepository(User::class)->findOneBy(['changePasswordToken' => $token]);

            $redirectNullUser = $this->checkUser($user, 'Token Unknown', 'login');
            if($redirectNullUser) return $redirectNullUser;
            

            if ($request->request->get('password') === $request->request->get('passwordVerify')) {
                $user->setPassword($passwordEncoder->encodePassword($user, $request->request->get('password')));
                $user->setChangePasswordToken(null);
                $user->setUpdatedAt();
                $this->em->persist($user);
                $this->em->flush();
                $this->addFlash('notice', 'Mot de passe modifié');
                return $this->redirectToRoute('login');
            } else {
                $this->addFlash('danger', 'Vos mots de passe sont différents');
                return $this->redirectToRoute('reset_password', ['token' => $token]);
            }
        }
        return $this->render('security/resetPassword.html.twig', ['token' => $token]);
    }

    private function sendMessage($mailer, $user, $titleMail, $messageMail)
    {
        $message = (new \Swift_Message($titleMail))
            ->setFrom('2biolibre@gmail.com')
            ->setTo($user->getMail())
            ->setBody(
                $messageMail,
                'text/html'
            );

        // this line counts the number of mail sent
        return $mailer->send($message);
    }

    private function checkUser($user, $flashMessage, $routeToRedirect)
    {
        if ($user === null) {
            $this->addFlash('danger', $flashMessage);
            return $this->redirectToRoute($routeToRedirect);
        }
        return null;
    }
}
