<?php 
declare(strict_types=1);
/**
 * Helper functions 
 */

 /**
  * Dump and die
  *
  * @param mixed ...$variables
  * @return void
  */
function dd(mixed ...$variables){
    foreach($variables as $variable) {
        echo "<pre style='background-color: #f7f7f7; color: #333; padding: 10px; border: 1px solid #ccc; font-size: 14px;overflow-wrap:break-word;white-space: pre-wrap;'>";
        // Print variable in a readable format
        if (is_object($variable) || is_array($variable)) {
            print_r($variable);
        } else {
            var_dump($variable);
        }
        echo "</pre>";
    }
    // Stop script execution
    die();
}

 /**
  * Dump
  *
  * @param mixed ...$variables
  * @return void
  */
  function dump(mixed ...$variables){
    foreach($variables as $variable) {
        echo "<pre style='background-color: #f7f7f7; color: #333; padding: 10px; border: 1px solid #ccc; font-size: 14px;overflow-wrap:break-word;white-space: pre-wrap;'>";
        // Print variable in a readable format
        if (is_object($variable) || is_array($variable)) {
            print_r($variable);
        } else {
            var_dump($variable);
        }
        echo "</pre>";
    }
}

/**
 * Generate a clean relative URL from the root
 *
 * @param string $path The relative path (e.g., "css/styles.css")
 * @param array $query Optional query parameters as an associative array
 * @return string The formatted URL
 */
function url(string $path, array $query = []): string {
    $cleanPath = '/' . ltrim(rtrim($path, '/'), '/');
    return empty($query) ? $cleanPath : $cleanPath . '?' . http_build_query($query);
}

/**
 * Returns absolute URL based on server environment
 *
 * @param string $path
 * @return string
 */
function absolute_url(string $path): string {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $protocol . "://" . $host . url($path);
}

/**
 * Generate a CSRF token for forms
 *
 * @return string The generated token
 */
function generate_csrf_token(): string {
    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $token;
    return $token;
}

/**
 * Verify a CSRF token
 *
 * @param string $token The token to verify
 * @return bool True if valid, false otherwise
 */
function verify_csrf_token(string $token): bool {
    return isset($_SESSION['csrf_token']) && $_SESSION['csrf_token'] === $token;
}

/**
 * Convert a string to a slug
 *
 * @param string $string The string to convert
 * @return string The resulting slug
 */
function slugify(string $string): string {
    $string = strtolower(trim($string));
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return $string;
}