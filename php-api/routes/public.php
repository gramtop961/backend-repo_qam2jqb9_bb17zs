<?php
require_once __DIR__ . '/../controllers/ProductsController.php';
require_once __DIR__ . '/../controllers/CategoriesController.php';
require_once __DIR__ . '/../controllers/BrandsController.php';
require_once __DIR__ . '/../controllers/ContactController.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') { send_json(['ok'=>true]); }

switch (true) {
    case preg_match('#/api/products$#', $uri) && $method === 'GET':
        ProductsController::list($_GET);
        break;
    case preg_match('#/api/product$#', $uri) && $method === 'GET':
        if (!isset($_GET['id'])) send_json(['error'=>'Missing id'], 422);
        ProductsController::get(intval($_GET['id']));
        break;
    case preg_match('#/api/categories$#', $uri) && $method === 'GET':
        CategoriesController::list();
        break;
    case preg_match('#/api/brands$#', $uri) && $method === 'GET':
        BrandsController::list();
        break;
    case preg_match('#/api/contact-message$#', $uri) && $method === 'POST':
        ContactController::submit(json_input());
        break;
    default:
        return false; // let next router handle
}
