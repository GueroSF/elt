<?php
declare(strict_types=1);

namespace App\Service;


use App\Interfaces\Guestbook\GuestbookInterface;
use App\Interfaces\Guestbook\PostInterface;

class GuestbookService
{
    private GuestbookInterface $guestbook;
    private int $delaySecond;

    public function __construct(GuestbookInterface $guestbook, int $delaySecond)
    {
        $this->guestbook = $guestbook;
        $this->delaySecond = $delaySecond;
    }

    public function getPosts(): array
    {
        $posts = [];
        $comments = [];

        /** @var PostInterface $post */
        foreach ($this->guestbook->getAllPosts() as $post) {
            if (null === $parentId = $post->getParentId()) {
                $posts[$post->getId()] = $post;
            } else {
                if (!isset($comments[$parentId])) {
                    $comments[$parentId] = [];
                }

                $comments[$parentId][] = $post;

            }
        }

        return [$posts, $comments];
    }

    public function canWriteMessage(): bool
    {
       if (null === $lastPost = $this->guestbook->getLastPost()) {
           return true;
       }

       return $lastPost->getCreatedAt()->diff(new \DateTime('now'))->s > $this->delaySecond;
    }

    public function updateMessage(int $postId, string $message): void
    {
        $this->guestbook->updatePost($postId, $message);
    }

    public function createComment(int $parentId, string $message): PostInterface
    {
        return $this->guestbook->createComment($parentId, $message);
    }

    public function createPost(string $message): PostInterface
    {
        return $this->guestbook->createPost($message);
    }

}
