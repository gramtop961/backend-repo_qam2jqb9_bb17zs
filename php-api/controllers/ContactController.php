<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers.php';

class ContactController {
    public static function submit($data) {
        global $pdo;
        require_fields($data, ['name','email','message']);
        $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, phone, message) VALUES (?,?,?,?)");
        $stmt->execute([
            sanitize_string($data['name']),
            sanitize_string($data['email']),
            $data['phone'] ?? null,
            $data['message']
        ]);
        send_json(['success'=>true]);
    }
}
