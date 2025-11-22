<?php
require_once __DIR__ . '/config.php';

function jwt_encode($payload, $secret) {
    $header = ['alg' => 'HS256', 'typ' => 'JWT'];
    $segments = [];
    $segments[] = rtrim(strtr(base64_encode(json_encode($header)), '+/', '-_'), '=');
    $segments[] = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');
    $signing_input = implode('.', $segments);
    $signature = hash_hmac('sha256', $signing_input, $secret, true);
    $segments[] = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
    return implode('.', $segments);
}

function jwt_decode($jwt, $secret) {
    $parts = explode('.', $jwt);
    if (count($parts) !== 3) return null;
    [$head, $body, $sig] = $parts;
    $signing_input = $head . '.' . $body;
    $expected = rtrim(strtr(base64_encode(hash_hmac('sha256', $signing_input, $secret, true)), '+/', '-_'), '=');
    if (!hash_equals($expected, $sig)) return null;
    $payload = json_decode(base64_decode(strtr($body, '-_', '+/')), true);
    return $payload;
}

function require_auth() {
    global $config;
    $token = bearer_token();
    if (!$token) {
        send_json(['error' => 'Unauthorized'], 401);
    }
    $payload = jwt_decode($token, $config['jwt_secret']);
    if (!$payload || ($payload['exp'] ?? 0) < time()) {
        send_json(['error' => 'Token invalid or expired'], 401);
    }
    return $payload;
}
