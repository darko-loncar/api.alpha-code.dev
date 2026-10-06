<?php

namespace Crystal\Support;

final class UUID
{
    public static function generate(): string
    {
        $data = random_bytes(16);

        // Set version to 4.
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);

        // Set variant to RFC 4122.
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf(
            '%s%s-%s-%s-%s-%s%s%s',
            str_split(bin2hex($data), 4)
        );
    }
}
