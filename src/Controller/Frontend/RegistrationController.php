<?php

namespace App\Controller\Frontend;

use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Form\Type\UserType;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Twig\Environment;



class RegistrationController extends AbstractController
{

    public function __construct(Environment $twig, UserRepository $userRepository, EntityManagerInterface $em)
    {
        $this->twig = $twig;
        $this->userRepository = $userRepository;
        $this->em = $em;
    }


    /**
     * register
     * @Route("/registration", name="registration")
     * @param  mixed $request
     * @param  mixed $entityManager
     * @param  mixed $passwordEncoder
     * @return Response
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {

        $newUser = new User();

        $form = $this->createForm(UserType::class, $newUser);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $password = $passwordEncoder->encodePassword($newUser, $newUser->getPassword());
            $newUser->setPassword($password);
            $newUser->setRoles(['ROLE_USER']);
            $this->em->persist($newUser);
            $this->em->flush();
            $this->addFlash('newuser', 'Inscription réussie !');
            return $this->redirectToRoute('posts');
        }
        return $this->render("pages/registration.html.twig", [
            'form' => $form->createView(),
        ]);
    }
}
