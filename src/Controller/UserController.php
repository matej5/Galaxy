<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Form\UserFormType;
use App\Repository\UserRepository;
use App\Security\LoginAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Class UserController
 *
 * @package App\Controller
 *
 * Security annotation on login will throw 403 and on register route we use redirect to route.
 * Both examples are correct.
 */
class UserController extends AbstractController
{
    /**
     * @Symfony\Component\Routing\Annotation\Route("/login", name="app_login")
     * @param           AuthenticationUtils $authenticationUtils
     * @return          Response
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('home_index');
        } else {
            // get the login error if there is one
            $error = $authenticationUtils->getLastAuthenticationError();
            // last username entered by the user
            $lastUsername = $authenticationUtils->getLastUsername();

            return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
        }
    }

    private function insertData($em, $data)
    {
        $em->persist($data);
        $em->flush();
    }

    /**
     * @Symfony\Component\Routing\Annotation\Route("/profile", name="app_profile")
     * @param             Request $request
     * @param             EntityManagerInterface $entityManager
     * @return            null|Response
     */
    public function profile(
        Request $request,
        EntityManagerInterface $entityManager
    ) {

        if (!$this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('home_index');
        } else {
            $user = $this->getUser();

            $form = $this->createForm(UserFormType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->getUser()->setFirstname($form->get('firstname')->getData());
                $this->getUser()->setLastname($form->get('lastname')->getData());
                $entityManager->flush();
            }

            return $this->render(
                'user/index.html.twig',
                [
                    'form' => $form->createView(),
                    'user' => $user
                ]
            );
        }
    }

    /**
     * @Symfony\Component\Routing\Annotation\Route("/user/{id}", name="app_user")
     * @param               User $user
     * @param               UserRepository $userRepository
     * @return              null|Response
     */
    public function user(
        User $user,
        UserRepository $userRepository
    ) {

        if (!$this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('home_index');
        } else {
            $user = $userRepository->findOneBy(['id' => $user->getId()]);

            return $this->render(
                'user/view.html.twig',
                [
                    'user' => $user
                ]
            );
        }
    }

    /**
     * @Symfony\Component\Routing\Annotation\Route("/generate", name="app_generate")
     * @param              Request $request
     * @return             null|Response
     */
    public function generate(Request $request)
    {

        if (!$this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('home_index');
        } else {
            $user = $this->getUser();
            $user->createAvatar();
            $form = $this->createForm(UserFormType::class, $user);
            $form->handleRequest($request);

            return $this->redirectToRoute('app_profile');
        }
    }


    /**
     * @Symfony\Component\Routing\Annotation\Route("/logout", name="app_logout")
     */
    public function logout()
    {
    }
}