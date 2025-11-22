<?php
// Single-file front controller routing to public and admin routes
require_once __DIR__ . '/helpers.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Serve uploads directly if under /api/uploads
if (preg_match('#/api/uploads/(.+)$#', $uri, $m)) {
    $file = __DIR__ . '/uploads/' . basename($m[1]);
    if (file_exists($file)) {
        $mime = mime_content_type($file);
        header('Content-Type: ' . $mime);
        readfile($file);
        exit;
    } else {
        http_response_code(404);
        echo 'Not found';
        exit;
    }
}

// Try public routes
if (@include __DIR__ . '/routes/public.php') {
    exit; // handled
}
// Try admin routes
if (@include __DIR__ . '/routes/admin.php') {
    exit; // handled
}

send_json(['error' => 'Endpoint not found'], 404);
