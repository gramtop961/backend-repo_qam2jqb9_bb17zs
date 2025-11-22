<?php
// Common helper functions

function load_env($path) {
    if (!file_exists($path)) return [];
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $env = [];
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        [$key, $value] = array_map('trim', explode('=', $line, 2));
        $env[$key] = $value;
    }
    return $env;
}

function send_json($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    // Basic CORS (adjust as needed)
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    echo json_encode($data);
    exit;
}

function sanitize_string($str) {
    return trim(filter_var($str, FILTER_SANITIZE_STRING));
}

function sanitize_int($val) {
    return filter_var($val, FILTER_VALIDATE_INT);
}

function slugify($text) {
    $text = preg_replace('~[\p{Pd}\s]+~u', '-', $text);
    $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    return strtolower($text ?: 'n-a');
}

function json_input() {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function require_fields($data, $fields) {
    foreach ($fields as $f) {
        if (!isset($data[$f]) || $data[$f] === '') {
            send_json(['error' => "Missing field: $f"], 422);
        }
    }
}

function bearer_token() {
    $headers = getallheaders();
    if (isset($headers['Authorization']) && preg_match('/Bearer\s(.*)$/i', $headers['Authorization'], $matches)) {
        return $matches[1];
    }
    return null;
}
