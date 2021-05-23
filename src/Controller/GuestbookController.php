<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\GuestbookService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GuestbookController extends AbstractController
{
    #[Route('/guestbook', name: 'guestbook_index', methods: ['GET'])]
    public function index(GuestbookService $guestbookService): Response
    {
        [$posts, $comments] = $guestbookService->getPosts();

        return $this->render(
            'guestbook.html.twig',
            [
                'canSave' => $guestbookService->canWriteMessage(),
                'posts' => $posts,
                'comments' => $comments,
            ]
        );
    }

    #[Route('/guestbook/create-post', name: 'guestbook_create_post', methods: ['POST'])]
    public function createPost(Request $request, GuestbookService $guestbookService): Response
    {
        $guestbookService->createPost($request->request->get('text'));

        return $this->redirectToRoute('guestbook_index');
    }

    #[Route('/guestbook/update-post', name: 'guestbook_update_post', methods: ['POST'])]
    public function updatePost(Request $request, GuestbookService $guestbookService): Response
    {
        $guestbookService->updateMessage(
            (int) $request->request->get('post_id'),
            $request->request->get('text')
        );

        return $this->redirectToRoute('guestbook_index');
    }

    #[Route('/guestbook/create-comment', name: 'guestbook_create_comment', methods: ['POST'])]
    public function createComment(Request $request, GuestbookService $guestbookService): Response
    {
        $guestbookService->createComment(
            (int) $request->request->get('parent_id'),
            $request->request->get('comment')
        );

        return $this->redirectToRoute('guestbook_index');
    }
}
