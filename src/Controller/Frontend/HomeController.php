<?php

namespace App\Controller\Frontend;

use App\Entity\Post;
use App\Entity\User;
use Twig\Environment;
use App\Entity\Comment;
use App\Form\Type\SendCommentType;
use App\Repository\PostRepository;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    /**
     * index
     * @Route("/", name="posts")
     * @param  mixed $repository
     * @return Response
     */
    public function posts(PostRepository $postRepository): Response
    {

        return new Response($this->render('pages/home.html.twig', [
            'posts' => $postRepository->findAll(),
        ]));
    }
}
