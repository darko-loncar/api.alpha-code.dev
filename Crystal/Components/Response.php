<?php
declare(strict_types=1);

namespace Crystal\Components;

/**
 * HTTP response utility for sending JSON, plain-text, no-content, and redirect responses.
 *
 * Provides static methods for generating common HTTP responses with configurable
 * status codes and additional headers. JSON responses are encoded using UTF-8
 * with unescaped Unicode and slashes, and encoding errors raise exceptions.
 *
 * Response methods terminate script execution after sending the response,
 * except for redirect(), which sets the response status and Location header
 * without explicitly terminating execution.
 *
 * @package Crystal\Components
 */

final class Response
{
    /**
     * JSON response
     *
     * @param array $data
     * @param int $status
     * @param array $headers
     * @return void
     */
    public static function json(array $data, int $status = 200, array $headers = []): void
    {
        http_response_code($status);
        header("Content-Type: application/json; charset=UTF-8");
        foreach ($headers as $value) {
            header($value);
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        exit();
    }

    /**
     * Text response
     *
     * @param array $data
     * @param int $status
     * @param array $headers
     * @return void
     */
    public static function text(string $data, int $status = 200, array $headers = []): void
    {
        http_response_code($status);
        header("Content-Type: text/plain; charset=UTF-8");
        foreach ($headers as $value) {
            header($value);
        }
        echo $data;
        exit();
    }

    /**
     * No Content response
     *
     * @param array $headers
     * @return void
     */
    public static function noContent(array $headers = []): void
    {
        http_response_code(204);
        foreach ($headers as $value) {
            header($value);
        }
        exit();
    }

    /**
     * Redirect response
     *
     * @param array $data
     * @param int $status
     * @param array $headers
     * @return void
     */
    public static function redirect(string $url, int $status = 302, array $headers = []): void
    {
        http_response_code($status);
        header("Location: {$url}");
        foreach ($headers as $header) {
            header($header);
        };
    }

    /**
     * File download response
     *
     * Sends an existing file to the client as a downloadable attachment.
     * The MIME type must be explicitly specified, and the download filename
     * defaults to the basename of the provided file path.
     *
     * @param string $path Path to the file to download.
     * @param string $mimeType MIME type of the file.
     * @param string|null $filename Optional filename presented to the client.
     * @param array $headers Additional HTTP headers.
     * @return void

     * @throws \InvalidArgumentException If the MIME type or filename is invalid.
     * @throws \RuntimeException If the file does not exist or cannot be read.
     */
    public static function download(string $path, string $mimeType, ?string $filename = null, array $headers = []): void
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new \RuntimeException('File not found or not readable.');
        }

        if (
            trim($mimeType) === '' ||
            preg_match('/[\r\n]/', $mimeType)
        ) {
            throw new \InvalidArgumentException('Invalid MIME type.');
        }

        $filename ??= basename($path);

        if (
            $filename === '' ||
            preg_match('/[\x00-\x1F\x7F\/\\\\]/', $filename)
        ) {
            throw new \InvalidArgumentException('Invalid download filename.');
        }

        http_response_code(200);
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' .
            addcslashes($filename, '"\\') . '"; filename*=UTF-8\'\'' .
            rawurlencode($filename));
        header('Content-Length: ' . filesize($path));
        header('X-Content-Type-Options: nosniff');

        foreach ($headers as $header) {
            header($header);
        }

        readfile($path);
        exit();
    }
}
