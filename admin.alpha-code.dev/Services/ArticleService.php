<?php
declare(strict_types=1);

namespace App\Services;

use App\Databases\MainDb;
use Crystal\Components\Query\Query;
use Crystal\Support\Slug;

class ArticleService
{
    public function __construct(private MainDb $db)
    {
    }

    public function all(int $limit = 100): array
    {
        $query = $this->articleQuery()
            ->orderBy('article.id', 'desc')
            ->limit($limit);

        return $this->db->getAll($query->getQuery(), $query->getBindings());
    }

    public function find(int $id): ?array
    {
        $query = $this->articleQuery()->where('article.id', $id);
        return $this->db->first($query->getQuery(), $query->getBindings());
    }

    public function published(int $limit = 100): array
    {
        $query = $this->articleQuery()
            ->where('article.is_published', true)
            ->orderBy('article.id', 'desc')
            ->limit($limit);

        return $this->db->getAll($query->getQuery(), $query->getBindings());
    }

    public function findPublished(int $id): ?array
    {
        $query = $this->articleQuery()
            ->where('article.id', $id)
            ->where('article.is_published', true);

        return $this->db->first($query->getQuery(), $query->getBindings());
    }

    public function findAuthor(string $uuid): ?array
    {
        $query = Query::select('user')->where('user.uuid', $uuid);
        return $this->db->first($query->getQuery(), $query->getBindings());
    }

    /** @return array{0: array{title: string, slug: string, content: string, is_published: bool}, 1: list<string>} */
    public function validateInput(array $input): array
    {
        $title = $this->inputString($input, 'title');
        $slugInput = $this->inputString($input, 'slug');
        $article = [
            'title' => $title,
            'slug' => Slug::slugify($slugInput !== '' ? $slugInput : $title),
            'content' => $this->inputString($input, 'content'),
            'is_published' => ($input['is_published'] ?? '') === '1',
        ];

        $errors = [];
        if ($article['title'] === '') {
            $errors[] = 'Title is required.';
        }
        if ($article['slug'] === '') {
            $errors[] = 'A valid slug is required.';
        }
        if ($article['content'] === '') {
            $errors[] = 'Article content is required.';
        }

        return [$article, $errors];
    }

    public function create(array $article, int $authorId): void
    {
        $now = date('Y-m-d H:i:s');
        $insert = Query::insert('article')
            ->set('user_id', $authorId)
            ->set('title', $article['title'])
            ->set('slug', $article['slug'])
            ->set('content', $article['content'])
            ->set('is_published', $article['is_published'])
            ->set('created_at', $now)
            ->set('updated_at', $now)
            ->set('published_at', $article['is_published'] ? $now : null);

        $this->db->getAll($insert->getQuery(), $insert->getBindings());
    }

    public function update(int $id, array $article, array $existingArticle): void
    {
        $wasPublished = (bool) $existingArticle['is_published'];
        $now = date('Y-m-d H:i:s');
        $publishedAt = $article['is_published']
            ? ($wasPublished ? $existingArticle['published_at'] : $now)
            : null;

        $query = Query::update('article')
            ->set('title', $article['title'])
            ->set('slug', $article['slug'])
            ->set('content', $article['content'])
            ->set('is_published', $article['is_published'])
            ->set('published_at', $publishedAt)
            ->set('updated_at', $now)
            ->where('id', $id);

        $this->db->getAll($query->getQuery(), $query->getBindings());
    }

    public function delete(int $id): void
    {
        $this->db->getAll('DELETE FROM `article` WHERE id = ?', [$id]);
    }

    private function articleQuery(): \Crystal\Components\Query\Select
    {
        return Query::select('article')
            ->leftJoin('user', 'user_id', 'id', ['first_name', 'last_name']);
    }

    private function inputString(array $input, string $key): string
    {
        $value = $input[$key] ?? '';
        return is_scalar($value) ? trim((string) $value) : '';
    }
}
