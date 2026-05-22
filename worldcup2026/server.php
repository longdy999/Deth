<?php
/**
 * Laravel router for the PHP built-in web server (`php -S`).
 * Serves static files from /public if they exist, otherwise hands the
 * request off to public/index.php so Laravel can handle it.
 */
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

if ($uri !== '/' && file_exists(__DIR__ . '/public' . $uri)) {
    return false; // let the built-in server serve the static file
}

require_once __DIR__ . '/public/index.php';
