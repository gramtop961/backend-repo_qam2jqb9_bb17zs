<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../middleware.php';

class AuthController {
    public static function login($data) {
        global $pdo, $config;
        require_fields($data, ['username','password']);
        $stmt = $pdo->prepare('SELECT id, username, password FROM admin WHERE username = ?');
        $stmt->execute([sanitize_string($data['username'])]);
        $user = $stmt->fetch();
        if (!$user || !password_verify($data['password'], $user['password'])) {
            send_json(['error' => 'Invalid credentials'], 401);
        }
        $payload = [
            'sub' => $user['id'],
            'username' => $user['username'],
            'iat' => time(),
            'exp' => time() + $config['jwt_expires']
        ];
        $token = jwt_encode($payload, $config['jwt_secret']);
        send_json(['token' => $token, 'user' => ['id' => $user['id'], 'username' => $user['username']]]);
    }
}
