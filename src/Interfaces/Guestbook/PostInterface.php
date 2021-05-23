<?php
declare(strict_types=1);

namespace App\Interfaces\Guestbook;


interface PostInterface
{
    public function getId(): int;

    public function getParentId(): ?int;

    public function getText(): string;

    public function getCreatedAt(): \DateTimeInterface;
}
