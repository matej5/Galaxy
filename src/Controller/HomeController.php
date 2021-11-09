<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends AbstractController
{
    /**
     * @Symfony\Component\Routing\Annotation\Route("/", name="home_index")
     * @param      Request $request
     * @param      EntityManagerInterface $entityManager
     * @return       \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request, EntityManagerInterface $entityManager)
    {
        return $this->render(
            'home/index.html.twig',
            [
            ]
        );
    }

    /**
     * @Symfony\Component\Routing\Annotation\Route("/view/{id}", name="post_view")
     * @param               Post $post
     * @param               Request $request
     * @param               EntityManagerInterface $entityManager
     * @param               LikeDislikeRepository $likeDislikeRepository
     * @return                \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function show(
        Post $post,
        Request $request,
        EntityManagerInterface $entityManager,
        LikeDislikeRepository $likeDislikeRepository
    ) {
        $form = $this->createForm(CommentFormType::class);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_USER') && $form->isSubmitted() && $form->isValid()) {
            /**
             * @var Comment $comment
             */
            $comment = $form->getData();
            $comment->setUser($this->getUser());
            $post->addComment($comment);
            $date = new \DateTime("now");
            $comment->setCreatedAt($date);
            $entityManager->flush();
            return $this->redirectToRoute(
                'post_view',
                [
                    'id' => $post->getId()
                ]
            );
        }

        $userLikesPost = $likeDislikeRepository->findBy(
            [
                'user' => $this->getUser(),
                'post' => $post
            ]
        );

        return $this->render(
            'post/view.html.twig',
            [
                'post' => $post,
                'commentForm' => $form->createView(),
                'userLikesPost' => $userLikesPost
            ]
        );
    }

    /**
     * @Sensio\Bundle\FrameworkExtraBundle\Configuration\Security("user             == post.getUser()")
     * @Symfony\Component\Routing\Annotation\Route("/post/{id}/delete", name="post_delete")
     * @param                      Post $post
     * @param                      EntityManagerInterface $entityManager
     * @return                     \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deletePost(Post $post, EntityManagerInterface $entityManager)
    {
        if ($this->isGranted('ROLE_ADMIN') ||
            ($this->isGranted('ROLE_BOSS') &&
                $post->getUser() == $this->getUser())
        ) {
            $entityManager->remove($post);
            $entityManager->flush();
            $this->addFlash('success', 'Successfully deleted!');
            return $this->redirectToRoute('post_index');
        } else {
            return $this->redirectToRoute('post_index');
        }
    }

    /**
     * @return string
     */
    private function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }
}