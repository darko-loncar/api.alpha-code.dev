<?php
declare(strict_types=1);
namespace Crystal\Components;

final class Response
{
    public static function json(array $data, int $status = 200, array $headers = [])
    {
            http_response_code($status);
            header("Content-Type: application/json; charset=UTF-8");
            foreach ($headers as $value) {
                header($value);
                }
            echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    public static function text(string $data, int $status = 200, array $headers = [])
    {
            http_response_code($status);
            header("Content-Type: text/plain; charset=UTF-8");
            foreach ($headers as $value) {
                header($value);
                }
            echo $data;
    }

    public static function noContent(array $headers = [])
    {
            http_response_code(204);
            foreach ($headers as $value) {
                header($value);
                }
    }

    public static function redirect(string $url, int $status = 302, array $headers = []) {
            http_response_code($status);
            header("Location: {$url}");
            foreach ($headers as $header) {
                header($header);
        };
    }
}
