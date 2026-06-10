<?php
declare(strict_types=1);

namespace Services;

class ExchangeRateFetcher 
{
    public static function fetch() {

        // Set API URL
        $apiUrl = "https://api.frankfurter.dev/v1/latest";
        
        // Initialize cURL
        $ch = curl_init($apiUrl);
        
        // Set cURL options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Get response as a string
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification (optional)
        
        // Execute request and store response
        $response = curl_exec($ch);
        
        // Check for errors
        if (curl_errno($ch)) {
            echo 'cURL error: ' . curl_error($ch);
        } else {
            // Decode JSON response
            $data = json_decode($response, true);
        }
        
        // Close cURL session
        curl_close($ch);

        return $data;
    }
}