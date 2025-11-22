<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers.php';

class ProductsController {
    public static function list($query) {
        global $pdo;
        $sql = "SELECT p.*, b.name AS brand, c.name AS category FROM products p
                JOIN brands b ON p.brand_id=b.id
                JOIN categories c ON p.category_id=c.id WHERE 1=1";
        $params = [];
        if (!empty($query['brand'])) {
            $sql .= " AND b.slug = :brand";
            $params[':brand'] = sanitize_string($query['brand']);
        }
        if (!empty($query['category'])) {
            $sql .= " AND c.slug = :category";
            $params[':category'] = sanitize_string($query['category']);
        }
        if (!empty($query['availability'])) {
            $sql .= " AND p.availability = :availability";
            $params[':availability'] = $query['availability'] === 'in_stock' ? 'in_stock' : 'out_of_stock';
        }
        if (!empty($query['min_price'])) {
            $sql .= " AND p.price >= :min_price";
            $params[':min_price'] = floatval($query['min_price']);
        }
        if (!empty($query['max_price'])) {
            $sql .= " AND p.price <= :max_price";
            $params[':max_price'] = floatval($query['max_price']);
        }
        if (!empty($query['q'])) {
            $sql .= " AND (p.name LIKE :q OR p.model LIKE :q OR p.short_description LIKE :q OR p.long_description LIKE :q)";
            $params[':q'] = '%' . sanitize_string($query['q']) . '%';
        }
        $sort = 'p.created_at DESC';
        if (!empty($query['sort'])) {
            if ($query['sort'] === 'price_asc') $sort = 'p.price ASC';
            if ($query['sort'] === 'price_desc') $sort = 'p.price DESC';
            if ($query['sort'] === 'newest') $sort = 'p.created_at DESC';
        }
        $page = max(1, intval($query['page'] ?? 1));
        $perPage = min(60, max(1, intval($query['per_page'] ?? 12)));
        $offset = ($page - 1) * $perPage;

        $countSql = "SELECT COUNT(*) FROM products p
                     JOIN brands b ON p.brand_id=b.id
                     JOIN categories c ON p.category_id=c.id WHERE 1=1" . substr($sql, strpos($sql, 'AND'));
        $stmt = $pdo->prepare($sql . " ORDER BY $sort LIMIT :limit OFFSET :offset");
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll();

        $countStmt = $pdo->prepare(str_replace(['SELECT p.*, b.name AS brand, c.name AS category', ' ORDER BY '.$sort.' LIMIT :limit OFFSET :offset'], ['SELECT COUNT(*) AS cnt', ''], $sql));
        foreach ($params as $k => $v) {
            $countStmt->bindValue($k, $v);
        }
        $countStmt->execute();
        $total = intval($countStmt->fetchColumn());

        send_json(['items' => $items, 'page' => $page, 'per_page' => $perPage, 'total' => $total]);
    }

    public static function get($id) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT p.*, b.name AS brand, c.name AS category FROM products p
                JOIN brands b ON p.brand_id=b.id JOIN categories c ON p.category_id=c.id WHERE p.id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) send_json(['error' => 'Not found'], 404);
        send_json($row);
    }

    public static function add($data) {
        global $pdo;
        require_fields($data, ['name','brand','category','model']);
        $brand = self::ensureBrand($data['brand']);
        $category = self::ensureCategory($data['category']);
        $stmt = $pdo->prepare("INSERT INTO products (name, brand_id, category_id, model, price, short_description, long_description, specs, image, availability) VALUES (?,?,?,?,?,?,?,?,?,?)");
        $price = isset($data['price']) && $data['price'] !== '' ? floatval($data['price']) : null;
        $specs = isset($data['specs']) ? json_encode($data['specs']) : null;
        $image = $data['image'] ?? null;
        $availability = in_array($data['availability'] ?? 'in_stock', ['in_stock','out_of_stock']) ? $data['availability'] : 'in_stock';
        $stmt->execute([
            sanitize_string($data['name']), $brand, $category,
            sanitize_string($data['model']), $price, 
            $data['short_description'] ?? null, $data['long_description'] ?? null, $specs, $image, $availability
        ]);
        send_json(['success'=>true, 'id'=>$pdo->lastInsertId()]);
    }

    public static function update($data) {
        global $pdo;
        require_fields($data, ['id']);
        $id = intval($data['id']);
        $fields = ['name','model','price','short_description','long_description','specs','image','availability'];
        $sets = [];
        $params = [];
        foreach ($fields as $f) {
            if (isset($data[$f])) {
                $sets[] = "$f = :$f";
                $params[":$f"] = $f === 'price' ? ($data[$f] !== '' ? floatval($data[$f]) : null) : ($f==='specs' ? json_encode($data[$f]) : $data[$f]);
            }
        }
        if (isset($data['brand'])) {
            $brandId = self::ensureBrand($data['brand']);
            $sets[] = "brand_id = :brand_id";
            $params[':brand_id'] = $brandId;
        }
        if (isset($data['category'])) {
            $categoryId = self::ensureCategory($data['category']);
            $sets[] = "category_id = :category_id";
            $params[':category_id'] = $categoryId;
        }
        if (!$sets) send_json(['error' => 'No fields to update'], 422);
        $sql = "UPDATE products SET " . implode(', ', $sets) . " WHERE id = :id";
        $params[':id'] = $id;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        send_json(['success'=>true]);
    }

    public static function delete($data) {
        global $pdo;
        require_fields($data, ['id']);
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([intval($data['id'])]);
        send_json(['success'=>true]);
    }

    private static function ensureBrand($name) {
        global $pdo;
        $slug = slugify($name);
        $stmt = $pdo->prepare("SELECT id FROM brands WHERE slug = ?");
        $stmt->execute([$slug]);
        $id = $stmt->fetchColumn();
        if ($id) return intval($id);
        $stmt = $pdo->prepare("INSERT INTO brands (name, slug) VALUES (?,?)");
        $stmt->execute([$name, $slug]);
        return intval($pdo->lastInsertId());
    }

    private static function ensureCategory($name) {
        global $pdo;
        $slug = slugify($name);
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE slug = ?");
        $stmt->execute([$slug]);
        $id = $stmt->fetchColumn();
        if ($id) return intval($id);
        $stmt = $pdo->prepare("INSERT INTO categories (name, slug) VALUES (?,?)");
        $stmt->execute([$name, $slug]);
        return intval($pdo->lastInsertId());
    }
}
