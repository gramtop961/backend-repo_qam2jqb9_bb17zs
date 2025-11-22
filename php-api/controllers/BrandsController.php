<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers.php';

class BrandsController {
    public static function list() {
        global $pdo;
        $rows = $pdo->query("SELECT id, name, slug FROM brands ORDER BY name ASC")->fetchAll();
        send_json($rows);
    }
}
