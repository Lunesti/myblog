<?php

namespace App\Controller\Frontend;

use App\Entity\Post;
use App\Entity\User;
use Twig\Environment;
use App\Entity\Comment;
use App\Form\Type\CommentType;
use App\Repository\PostRepository;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{

    public function __construct(Environment $twig, PostRepository $postRepository, CommentRepository $commentRepository, EntityManagerInterface $em)
    {
        $this->twig = $twig;
        $this->postRepository = $postRepository;
        $this->commentRepository = $commentRepository;
        $this->em = $em;
    }

    /**
     * index
     * @Route("/posts", name="posts")
     *
     * @return Response
     */
    public function posts(): Response
    {

        return new Response($this->render('pages/home.html.twig', [
            'posts' => $this->postRepository->findAll(),
        ]));
    }

    /**
     * post
     * @Route("/posts/{slug}-{id}", name="post.show", requirements = {"slug": "[a-z0-9\-]*"}, methods="GET|POST")
     * @param Post $post
     * @param Request $request
     * @return Response
     */
    public function post(Post $post, string $slug, Request $request, PaginatorInterface $paginator): Response
    {
        if ($post->getSlug() !== $slug) {
            return  $this->redirectToRoute('post.show',  [
                'id' => $post->getId(),
                'slug' => $post->getSlug()
            ], 301);
        }

        //On crée un nouvel objet comment
        $comment = new Comment();

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $username = $this->getUser()->getUsername();
            $comment->setAuthor($username);
            $test = $comment->setPost($post);

            $this->addFlash(
                'notice',
                'Commentaire envoyé !'
            );
            $this->em->persist($comment);
            $this->em->flush();

            return $this->redirectToRoute('post.show', [
                'id' => $post->getId(),
                'slug' => $post->getSlug(),
                'test' => $test

            ]);
        }

        //Pour récupérer les commentaires correspondant à son article:

        //on va chercher dans le répo de la class Post l'id correspondant au post,
        $post = $this->em->getRepository(Post::class)->findOneBy(['id' => $post]);
        //ensuite on récupère l'id du post dans le repo de comment
       /*$comments = $this->em->getRepository(Comment::class)->findBy([
            'post' => $post
        ], [
            'created_at' => 'desc'
        ]);*/

        $comments = $paginator->paginate(
            $this->commentRepository->findBy([
                'post' => $post]),
            $request->query->get('page', 1),
            6
        );


        return new Response($this->render('pages/_post.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
            'comment' => $comments,
        ]));
    }
}
