<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\ArticleService;
use Crystal\Components\Response;

class APIController
{
    private const CORS_HEADERS = ['Access-Control-Allow-Origin: *'];

    public function __construct(private ArticleService $articles)
    {
    }

    public function articles(): void
    {
        Response::json(['data' => $this->articles->published()], 200, self::CORS_HEADERS);
    }

    public function article(int $id): void
    {
        $article = $this->articles->findPublished($id);
        if ($article === null) {
            Response::json(['error' => ['message' => 'Article not found.']], 404, self::CORS_HEADERS);
            return;
        }

        Response::json(['data' => $article], 200, self::CORS_HEADERS);
    }
}
