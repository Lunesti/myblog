<?php

namespace App\Controller\Admin;

use  App\Entity\Post;
use App\Form\Type\PostType;
use App\Entity\Comment;
use App\Form\Type\CommentType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class AdminController extends AbstractController
{
    public function __construct(PostRepository $repository, EntityManagerInterface $em)
    {
        $this->repository = $repository;
        $this->em = $em;
    }

    /**
     * create
     * @Route("/admin/post/create", name="admin.post.create")
     * @param  Request $request
     * @return Response
     */
    public function create(Request $request): Response
    {
        $post = new Post();

        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $this->em->persist($post);
            $this->em->flush();
            $this->addFlash('success', 'Article ajouté avec succès');

            return $this->redirectToRoute('posts');
        }

        return $this->render('admin/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    
    /**
     * edit
     * @Route("/admin/post/{id}", name="admin.post.edit", methods="GET|POST")
     * @param  Post $post
     * @param  Request $request
     * @return Response
     */
    public function update(Post $post, Request $request): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('success', 'Article modifié avec succès');
            return $this->redirectToRoute('posts');
        }

        return $this->render("admin/edit.html.twig", [
            'form' => $form->createView(),
            'post' => $post,
        ]);
    }
    
    /**
     * delete
     * @Route("/admin/post/{id}", name="admin.post.delete", methods="DELETE")
     * @param  Post $post
     */
    public function delete(Post $post, Request $request): Response {
        $submittedToken = $request->get('_token');
        if ($this->isCsrfTokenValid('delete' . $post->getId(), $submittedToken)) {
            $this->em->remove($post);
            $this->em->flush();
            $this->addFlash('success', 'Bien supprimé avec succès');
        }
        return $this->redirectToRoute('posts');

    }
}
