<?php
declare(strict_types=1);

namespace Core;

class Request
{
    public static function getUri(): string
    {
        return strtok($_SERVER['REQUEST_URI'], '?'); // safer
    }

    public static function getHttpMethod(): string
    {
        return $_SERVER["REQUEST_METHOD"];
    }

    public static function getPostData(): object
    {
        return (object)static::sanitizeData($_POST);
    }

    public static function getQueryData(): object
    {
        return (object)static::sanitizeData($_GET);
    }

    public static function getJson(): ?object
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);

        if (!is_array($data)) {
            return null;
        }

        return (object)static::sanitizeData($data);
    }

    private static function sanitizeData(array $requestData): array
    {
        $returnRequestData = [];

        foreach ($requestData as $key => $value) {
            $normalizedKey = str_replace('-', '_', $key);
            $returnRequestData[$normalizedKey] = static::sanitizeValue($value);
        }

        return $returnRequestData;
    }

    private static function sanitizeValue($value)
    {
        if (is_array($value)) {
            return array_map([static::class, 'sanitizeValue'], $value);
        }

        if (!is_string($value)) {
            return $value;
        }

        $data = trim($value);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');

        return $data;
    }
}
