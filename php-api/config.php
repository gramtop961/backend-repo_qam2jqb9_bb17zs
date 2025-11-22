<?php
require_once __DIR__ . '/helpers.php';

$env = load_env(__DIR__ . '/.env');

$config = [
    'app_env' => $env['APP_ENV'] ?? 'production',
    'app_debug' => filter_var($env['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'app_url' => $env['APP_URL'] ?? '',
    'jwt_secret' => $env['JWT_SECRET'] ?? 'change_me',
    'jwt_expires' => intval($env['JWT_EXPIRES'] ?? 86400),
    'upload_dir' => __DIR__ . '/' . ($env['UPLOAD_DIR'] ?? 'uploads'),
];

if (!is_dir($config['upload_dir'])) {
    @mkdir($config['upload_dir'], 0755, true);
}

