<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/middleware/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id   = (int)$_POST['product_id'];
    $name         = trim($_POST['name']);
    $email        = trim($_POST['email'] ?? '');
    $phone        = trim($_POST['phone'] ?? '');
    $url          = trim($_POST['url'] ?? '');
    $rating       = max(0, min(5, (int)($_POST['rating'] ?? 0)));
    $shipping_days = (float)($_POST['shipping_days'] ?? 0);
    $notes        = trim($_POST['notes'] ?? '');

    if (empty($name) || $shipping_days < 0) {
        redirect_to('index.php?tab=saved&view_product=' . $product_id . '&error=db');
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO suppliers (product_id, name, email, phone, url, rating, shipping_days, notes)
            VALUES (:product_id, :name, :email, :phone, :url, :rating, :shipping_days, :notes)
        ");
        $stmt->bindValue(':product_id',    $product_id,   PDO::PARAM_INT);
        $stmt->bindValue(':name',          $name,         PDO::PARAM_STR);
        $stmt->bindValue(':email',         $email,        PDO::PARAM_STR);
        $stmt->bindValue(':phone',         $phone,        PDO::PARAM_STR);
        $stmt->bindValue(':url',           $url,          PDO::PARAM_STR);
        $stmt->bindValue(':rating',        $rating,       PDO::PARAM_INT);
        $stmt->bindValue(':shipping_days', $shipping_days);
        $stmt->bindValue(':notes',         $notes,        PDO::PARAM_STR);
        $stmt->execute();

        redirect_to('index.php?tab=saved&view_product=' . $product_id . '&supplier_saved=1');
    } catch (PDOException $e) {
        redirect_to('index.php?tab=saved&view_product=' . $product_id . '&error=db');
    }
} else {
    redirect_to('index.php');
}
?>