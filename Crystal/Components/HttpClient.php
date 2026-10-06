<?php
namespace Crystal\Components;

use \RuntimeException;

final class HttpClient
{
    public function get(string $url): string
    {
        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);

            throw new RuntimeException(
                "HTTP request failed: {$error}"
            );
        }

        $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

        if ($status < 200 || $status >= 300) {
            throw new RuntimeException(
                "HTTP request returned status {$status}"
            );
        }

        return $response;
    }
}
