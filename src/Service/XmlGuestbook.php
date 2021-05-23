<?php
declare(strict_types=1);

namespace App\Service;


use App\Interfaces\Guestbook\GuestbookInterface;
use App\Interfaces\Guestbook\PostInterface;
use App\Lib\PostDTO;
use XMLReader;

class XmlGuestbook implements GuestbookInterface
{
    private const ROOT_NODE = 'Guestbook';
    private const ITEM_NODE = 'Post';

    private ?XMLReader $reader = null;
    private string $file;
    private \DOMDocument $DOMDocument;

    public function __construct(string $file)
    {
        $this->file = $file;

        if (!file_exists($file)) {
            $this->createGuestbookStorage();
        }
    }

    public function getAllPosts(): iterable
    {
        $reader = $this->getXmlReader();

        while ($reader->read()) {
            if ($reader->nodeType === XMLReader::ELEMENT && $reader->name === self::ITEM_NODE) {
                yield $this->convertStringToDto($reader->readOuterXml());
            }
        }

        $this->readerClose();

        return [];
    }

    public function getLastPost(): ?PostInterface
    {
        if (null === $lastId = $this->findLastId()) {
            return null;
        }

        return $this->getPostById($lastId);
    }

    private function findLastId(): ?int
    {
        $reader = $this->getXmlReader();

        $lastId = null;
        while ($reader->read()) {
            if ($reader->nodeType === XMLReader::ELEMENT && $reader->name === self::ROOT_NODE) {
                $postId = $reader->getAttribute('lastPostId');
                if (is_numeric($postId)) {
                    $lastId = (int)$postId;
                }

                break;
            }
        }

        $this->readerClose();

        return $lastId;
    }

    public function getPostById(int $id): ?PostInterface
    {
        $reader = $this->getXmlReader();

        $reader->moveToElement();

        $idAsString = (string)$id;
        $result = null;
        while ($reader->read()) {
            if ($reader->nodeType === XMLReader::ELEMENT && $reader->name === self::ITEM_NODE) {
                if ($idAsString === $reader->getAttribute('id')) {
                    $result = $this->convertStringToDto($reader->readOuterXml());
                    break;
                }
            }
        }

        $this->readerClose();

        return $result;
    }

    public function updatePost(int $postId, string $message): void
    {
        $this->DOMDocument = new \DOMDocument();
        $this->DOMDocument->load($this->file);

        $postIdAsString = (string)$postId;

        /** @var \DOMElement $item */
        foreach ($this->DOMDocument->getElementsByTagName(self::ITEM_NODE) as $item) {
            if ($postIdAsString === $item->getAttribute('id')) {
                $text = $item->getElementsByTagName('Text')->item(0);
                $text->nodeValue = $message;
                break;
            }
        }

        $this->DOMDocument->save($this->file);
    }

    public function createPost(string $message): PostInterface
    {
        $lastId = (int)$this->findLastId();

        $postDto = $this->createPostDto(++$lastId, null, $message);

        $this->writePost($postDto);

        return $postDto;
    }

    public function createComment(int $parentId, string $comment): PostInterface
    {
        $lastId = (int)$this->findLastId();

        $postDto = $this->createPostDto(++$lastId, $parentId, $comment);

        $this->writePost($postDto);

        return $postDto;
    }

    private function writePost(PostDTO $postDTO): bool
    {
        $postId = (string)$postDTO->getId();

        $this->DOMDocument = new \DOMDocument();
        $this->DOMDocument->load($this->file);

        $postNode = $this->createNode(self::ITEM_NODE, null, ['id' => $postId]);
        $postNode->append(
            $this->createNode('Id', $postId),
            $this->createNode('Text', $postDTO->getText()),
            $this->createNode('ParentId', (string)$postDTO->getParentId()),
            $this->createNode('CreatedAt', (string)$postDTO->getCreatedAt()->getTimestamp())
        );

        $root = $this->DOMDocument->getElementsByTagName(self::ROOT_NODE)->item(0);
        $root->append($postNode);
        $root->setAttribute('lastPostId', $postId);

        $this->DOMDocument->save($this->file);

        return true;
    }

    private function createNode(string $localName, ?string $elementValue = null, array $attributes = []): \DOMElement
    {
        $node = $this->DOMDocument->createElement($localName);

        if (null !== $elementValue) {
            $node->nodeValue = $elementValue;
        }

        foreach ($attributes as $qualifiedName => $value) {
            $node->setAttribute($qualifiedName, $value);
        }

        return $node;
    }

    private function getXmlReader(): XMLReader
    {
        if (null === $this->reader) {
            /** @var XMLReader $reader */
            $reader = XmlReader::open($this->file);
            $this->reader = $reader;
        }

        return $this->reader;
    }

    private function readerClose(): void
    {
        if (null === $this->reader) {
            return;
        }

        $this->reader->close();
        $this->reader = null;
    }

    private function convertStringToDto(string $rawPost): PostInterface
    {
        $temp = (array)new \SimpleXMLElement($rawPost);

        $date = new \DateTime();
        $date->setTimestamp((int)$temp['CreatedAt']);

        $parentId = empty($temp['ParentId']) ? null : (int)$temp['ParentId'];

        return $this->createPostDto((int)$temp['Id'], $parentId, (string)$temp['Text'], $date);
    }

    private function createPostDto(
        int $id,
        ?int $parentId,
        string $text,
        ?\DateTimeInterface $createdAt = null
    ): PostDTO {
        if (null === $createdAt) {
            $createdAt = new \DateTime('now');
        }

        return new PostDTO($id, $parentId, $text, $createdAt);
    }

    private function createGuestbookStorage(): void
    {
        $this->DOMDocument = new \DOMDocument();
        $this->DOMDocument->append($this->createNode(self::ROOT_NODE, null, ['lastPostId' => '']));
        $this->DOMDocument->save($this->file);
    }
}
