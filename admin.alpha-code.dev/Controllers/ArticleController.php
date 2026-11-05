<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\ArticleService;
use App\Services\AuthService;
use Crystal\Components\Response;
use Crystal\Components\View;
use Throwable;

class ArticleController
{
    public function __construct(private ArticleService $articles, private AuthService $auth)
    {
    }

    public function index(): string
    {
        $this->auth->requireLogin();
        return View::render('articles', [
            'pageId' => 'dashboard',
            'articleData' => $this->articles->all(),
        ]);
    }

    public function newArticle(): string
    {
        $this->auth->requireLogin();
        return $this->renderEditor($this->emptyArticle(), true);
    }

    public function article(int $id): string
    {
        $this->auth->requireLogin();

        $article = $this->articles->find($id);
        if ($article === null) {
            Response::redirect('/articles');
            return '';
        }

        return $this->renderEditor($article, false);
    }

    public function create(): string
    {
        $this->auth->requireLogin();

        [$article, $errors] = $this->articles->validateInput($_POST);
        $author = $this->articles->findAuthor((string) ($_SESSION['user_uuid'] ?? ''));
        if ($author === null) {
            Response::redirect('/login');
            return '';
        }

        if ($errors !== []) {
            return $this->renderEditor($article, true, $errors);
        }

        try {
            $this->articles->create($article, (int) $author['id']);
        } catch (Throwable $exception) {
            return $this->renderEditor(
                $article,
                true,
                ['Unable to save the article. The slug may already be in use.']
            );
        }

        Response::redirect('/articles');
        return '';
    }

    public function update(int $id): string
    {
        $this->auth->requireLogin();

        $existingArticle = $this->articles->find($id);
        if ($existingArticle === null) {
            Response::redirect('/articles');
            return '';
        }

        [$article, $errors] = $this->articles->validateInput($_POST);
        if ($errors !== []) {
            return $this->renderEditor([...$existingArticle, ...$article], false, $errors);
        }

        try {
            $this->articles->update($id, $article, $existingArticle);
        } catch (Throwable $exception) {
            return $this->renderEditor(
                [...$existingArticle, ...$article],
                false,
                ['Unable to save the article. The slug may already be in use.']
            );
        }

        Response::redirect('/article/' . $id);
        return '';
    }

    public function delete(int $id): void
    {
        $this->auth->requireLogin();
        $this->articles->delete($id);
        Response::redirect('/articles');
    }

    /** @param array<string, mixed> $article @param list<string> $errors */
    private function renderEditor(array $article, bool $isNew, array $errors = []): string
    {
        return View::render('article', [
            'pageId' => 'dashboard',
            'article' => $article,
            'isNew' => $isNew,
            'errors' => $errors,
            'formAction' => $isNew ? '/article' : '/article/' . (int) ($article['id'] ?? 0),
        ]);
    }

    /** @return array<string, mixed> */
    private function emptyArticle(): array
    {
        return [
            'id' => null,
            'title' => '',
            'slug' => '',
            'content' => '',
            'is_published' => false,
            'created_at' => null,
            'updated_at' => null,
            'published_at' => null,
            'user__first_name' => '',
            'user__last_name' => '',
        ];
    }
}
