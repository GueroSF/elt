<?php
declare(strict_types=1);

namespace App\Lib;


use App\Interfaces\Guestbook\PostInterface;

class PostDTO implements PostInterface
{
    private int $id;
    private ?int $parentId;
    private string $text;
    private \DateTimeInterface $createdAt;

    public function __construct(int $id, ?int $parentId, string $text, \DateTimeInterface $createdAt)
    {
        $this->id = $id;
        $this->parentId = $parentId;
        $this->text = $text;
        $this->createdAt = $createdAt;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getParentId(): ?int
    {
        return $this->parentId;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

}
