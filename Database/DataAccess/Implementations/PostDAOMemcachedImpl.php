<?php
namespace Database\DataAccess\Implementations;

use Database\DataAccess\Interfaces\PostDAO;
use Database\DatabaseManager;
use Memcached;
use Models\Post;

class PostDAOMemcachedImpl implements PostDAO
{
    private Memcached $mem;

    public function __construct()
    {
        $this->mem = DatabaseManager::getMemcachedConnection();
    }

    public function create(Post $postData): bool
    {
        if ($postData->getId() !== null) {
            throw new \Exception("Cannot create a post that already has an ID.");
        }

        // Auto-increment style key. If not exists, start at 1.
        $id = $this->mem->increment('Post_auto_id', 1, 1);
        $postData->setId($id);

        $now = date('Y-m-d H:i:s');
        $postData->setCreatedAt($now);
        $postData->setUpdatedAt($now);

        return $this->mem->set("Post_$id", json_encode($this->toArray($postData)));
    }

    public function getById(int $id): ?Post
    {
        $raw = $this->mem->get("Post_$id");
        if ($raw === false) return null;
        return $this->fromArray(json_decode($raw, true));
    }

    public function update(Post $postData): bool
    {
        if ($postData->getId() === null) {
            throw new \Exception("Post has no ID.");
        }
        $postData->setUpdatedAt(date('Y-m-d H:i:s'));
        return $this->mem->set("Post_{$postData->getId()}", json_encode($this->toArray($postData)));
    }

    public function delete(int $id): bool
    {
        return $this->mem->delete("Post_$id");
    }

    public function createOrUpdate(Post $postData): bool
    {
        return $postData->getId() ? $this->update($postData) : $this->create($postData);
    }

    public function getAllThreads(int $offset, int $limit): array
    {
        // Simple O(n) scan of keys (OK for demo)
        $keys = $this->mem->getAllKeys() ?: [];
        $postKeys = array_values(array_filter($keys, fn($k) => str_starts_with($k, 'Post_')));

        // Load and filter where reply_to_id is null
        $posts = [];
        foreach ($postKeys as $k) {
            $raw = $this->mem->get($k);
            if ($raw === false) continue;
            $arr = json_decode($raw, true);
            if (!isset($arr['reply_to_id']) || $arr['reply_to_id'] === null) {
                $posts[] = $arr;
            }
        }

        // Sort by created_at desc
        usort($posts, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));

        // Slice & map
        $slice = array_slice($posts, $offset, $limit);
        return array_map([$this, 'fromArray'], $slice);
    }

    public function getReplies(Post $postData, int $offset, int $limit): array
    {
        $keys = $this->mem->getAllKeys() ?: [];
        $postKeys = array_values(array_filter($keys, fn($k) => str_starts_with($k, 'Post_')));

        $rows = [];
        foreach ($postKeys as $k) {
            $raw = $this->mem->get($k);
            if ($raw === false) continue;
            $arr = json_decode($raw, true);
            if (($arr['reply_to_id'] ?? null) === $postData->getId()) {
                $rows[] = $arr;
            }
        }

        usort($rows, fn($a, $b) => strcmp($a['created_at'] ?? '', $b['created_at'] ?? ''));

        $slice = array_slice($rows, $offset, $limit);
        return array_map([$this, 'fromArray'], $slice);
    }

    private function toArray(Post $p): array
    {
        return [
            'post_id'        => $p->getId(),
            'reply_to_id'    => $p->getReplyToId(),
            'subject'        => $p->getSubject(),
            'content'        => $p->getContent(),
            'image_path'     => $p->getImagePath(),
            'thumbnail_path' => $p->getThumbnailPath(),
            'created_at'     => $p->getCreatedAt(),
            'updated_at'     => $p->getUpdatedAt(),
        ];
    }

    private function fromArray(array $row): Post
    {
        return new Post(
            id: $row['post_id'] ?? null,
            replyToId: $row['reply_to_id'] ?? null,
            subject: $row['subject'] ?? null,
            content: $row['content'] ?? '',
            imagePath: $row['image_path'] ?? null,
            thumbnailPath: $row['thumbnail_path'] ?? null,
            createdAt: $row['created_at'] ?? null,
            updatedAt: $row['updated_at'] ?? null
        );
    }
}

