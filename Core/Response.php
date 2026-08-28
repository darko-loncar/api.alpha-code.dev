<?php
declare(strict_types=1);

namespace Core;

final class Response
{
    public function __construct(
        public readonly mixed $body,
        public readonly int $status = 200,
        public readonly string $contentType = 'application/json',
        public readonly array $headers = []
    ) {}

    public static function json(array $data, int $status = 200, array $headers = []): self
    {
        return new self(json_encode($data, JSON_THROW_ON_ERROR), $status, 'application/json', $headers);
    }

    public static function html(string $html, int $status = 200, array $headers = []): self
    {
        return new self($html,$status,'text/html',$headers);
    }

    public static function text(string $text,int $status = 200,array $headers = []): self 
    {
        return new self($text,$status,'text/plain',$headers);
    }

    public static function noContent(array $headers = []): self
    {
        return new self(null,204,'text/plain',$headers);
    }

    public static function error(string $message, int $status = 400, array $headers = []): self 
    {
        return new self(['success' => false, 'error' => $message], $status, 'application/json', $headers);
    }

    public static function redirect(string $url, int $status = 302, array $headers = []): self
    {
        return new self(
            body: null,
            status: $status,
            contentType: 'text/plain',
            headers: array_merge($headers, [
                'Location' => $url
            ])
        );
    }

    public function send(): void
    {
    if (!headers_sent()) {
            http_response_code($this->status);

            header("Content-Type: {$this->contentType}; charset=UTF-8");
            header('Cache-Control: no-cache, must-revalidate, max-age=0');
            header('Pragma: no-cache');

            foreach ($this->headers as $k => $v) {
                header("$k: $v");
            }
        }

        // 🚨 redirect handling (important)
        if (in_array($this->status, [301, 302, 303, 307, 308], true)) {
            return;
        }

        if ($this->status === 204 || $this->body === null) {
            return;
        }

        echo match ($this->contentType) {
            'application/json' => (string) $this->body,
            default => (string) $this->body,
        };
    }
}