<?php
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/ProductsController.php';
require_once __DIR__ . '/../controllers/UploadController.php';
require_once __DIR__ . '/../middleware.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') { send_json(['ok'=>true]); }

switch (true) {
    case preg_match('#/api/login$#', $uri) && $method === 'POST':
        AuthController::login(json_input());
        break;
    case preg_match('#/api/add-product$#', $uri) && $method === 'POST':
        require_auth();
        ProductsController::add(json_input());
        break;
    case preg_match('#/api/update-product$#', $uri) && $method === 'POST':
        require_auth();
        ProductsController::update(json_input());
        break;
    case preg_match('#/api/delete-product$#', $uri) && $method === 'POST':
        require_auth();
        ProductsController::delete(json_input());
        break;
    case preg_match('#/api/upload-image$#', $uri) && $method === 'POST':
        require_auth();
        UploadController::upload();
        break;
    default:
        return false; // let next router handle
}
