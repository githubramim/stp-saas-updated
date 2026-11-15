<?php

/**
 * Project-local router for `php -S`.
 *
 * Mirrors vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php
 * but drops the request-log write to `php://stdout`, which fails on Windows
 * (errno=22 EINVAL) after the dev server is auto-restarted, leaking a PHP
 * Notice into the HTTP response.
 */
$publicPath = getcwd();

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

// Emulate Apache's "mod_rewrite": serve existing files directly.
if ($uri !== '/' && file_exists($publicPath.$uri)) {
    return false;
}

require_once $publicPath.'/index.php';
