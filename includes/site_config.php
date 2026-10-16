<?php
/**
 * Shared public-site helpers (paths, assets, layout).
 * Keeps CSS/JS/links working from root and from folders like /contact-us/.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Web base path when the site is not at domain root (usually '').
 * Example: if files live in https://domain.com/pms/ then set '/pms'.
 */
if (!defined('PMS_WEB_BASE')) {
    define('PMS_WEB_BASE', '');
}

if (!defined('PMS_ASSET_VERSION')) {
    define('PMS_ASSET_VERSION', '20261016c');
}

function pms_base_path()
{
    return rtrim((string) PMS_WEB_BASE, '/');
}

/** Absolute-from-root URL, e.g. pms_url('style.css') => /style.css */
function pms_url($path = '')
{
    $path = ltrim((string) $path, '/');
    $base = pms_base_path();
    if ($path === '') {
        return $base === '' ? '/' : $base . '/';
    }
    return ($base === '' ? '/' : $base . '/') . $path;
}

/** Full origin+path for hard cases (emails, rare asset loads). */
function pms_absolute_url($path = '')
{
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $scheme = $https ? 'https' : 'http';
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'makeindiapms.com';
    return $scheme . '://' . $host . pms_url($path);
}

/** CSS/JS with cache-buster */
function pms_asset($path)
{
    return pms_url($path) . '?v=' . rawurlencode((string) PMS_ASSET_VERSION);
}

if (!function_exists('pms_h')) {
    function pms_h($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
