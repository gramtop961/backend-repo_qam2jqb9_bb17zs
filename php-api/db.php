<?php
require_once __DIR__ . '/helpers.php';

$env = load_env(__DIR__ . '/.env');
$DB_HOST = $env['DB_HOST'] ?? 'localhost';
$DB_NAME = $env['DB_NAME'] ?? 'automation_store';
$DB_USER = $env['DB_USER'] ?? 'root';
$DB_PASS = $env['DB_PASS'] ?? '';
$DB_CHARSET = $env['DB_CHARSET'] ?? 'utf8mb4';

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=$DB_CHARSET";
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
} catch (PDOException $e) {
    send_json(['error' => 'Database connection failed', 'details' => $e->getMessage()], 500);
}
