<?php

// Server environment takes precedence over the optional local .env file.
function app_env($name, $default = null) {
    if (isset($_ENV[$name])) {
        return $_ENV[$name];
    }
    if (isset($_SERVER[$name])) {
        return $_SERVER[$name];
    }
    $value = getenv($name);
    return $value !== false ? $value : $default;
}

$envFile = dirname(__DIR__) . '/.env';
if (is_readable($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') {
            continue;
        }
        if (!preg_match('/^([A-Z][A-Z0-9_]*)\s*=(.*)$/', $line, $matches)) {
            throw new RuntimeException('Invalid environment configuration line.');
        }
        $name = $matches[1];
        $value = trim($matches[2]);
        $length = strlen($value);
        if ($length >= 2 && (($value[0] === '"' && $value[$length - 1] === '"') ||
            ($value[0] === "'" && $value[$length - 1] === "'"))) {
            $value = substr($value, 1, -1);
        }
        if (app_env($name) === null) {
            $_ENV[$name] = $value;
        }
    }
}
unset($envFile, $line, $matches, $name, $value, $length);
