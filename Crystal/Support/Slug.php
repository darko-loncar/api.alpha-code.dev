<?php

namespace Crystal\Support;

final class Slug
{
    public static function slugify(string $string): string
    {
        $string = iconv(
            'UTF-8',
            'ASCII//TRANSLIT//IGNORE',
            trim($string)
        ) ?: '';

        $string = strtolower($string);
        $string = preg_replace('/[^a-z0-9]+/', '-', $string) ?: '';

        return trim($string, '-');
    }

}
