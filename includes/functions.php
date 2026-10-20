<?php

function e($value)
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect($url)
{
    header("Location: $url");
    exit;
}

/*
|--------------------------------------------------------------------------
| CSRF Protection
|--------------------------------------------------------------------------
*/

function csrfToken()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token)
{
    if (empty($_SESSION['csrf_token']) || empty($token) || 
        !hash_equals($_SESSION['csrf_token'], $token)) {
        die('Invalid CSRF token.');
    }
}