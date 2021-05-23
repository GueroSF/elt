<?php
declare(strict_types=1);

namespace App\Interfaces\Guestbook;


interface GuestbookInterface
{
    /**
     * @return PostInterface[]
     */
    public function getAllPosts(): iterable;

    public function getLastPost(): ?PostInterface;

    public function getPostById(int $id): ?PostInterface;

    public function createPost(string $message): PostInterface;

    public function updatePost(int $postId, string $message): void;

    public function createComment(int $parentId, string $comment): PostInterface;
}
