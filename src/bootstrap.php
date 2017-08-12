<?php

spl_autoload_register(function ($class) {
    $base = 'NetAngels\\';
    $file = str_replace([$base, '\\'], [__DIR__ . '/', '/'], $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});
