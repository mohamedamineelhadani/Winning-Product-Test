<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/middleware/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = (int)$_POST['product_id'];
    $link       = trim($_POST['link']);
    $notes      = trim($_POST['notes'] ?? '');

    if (empty($link)) {
        redirect_to('index.php?tab=saved&view_product=' . $product_id . '&error=db');
        
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO product_links (product_id, link, notes)
            VALUES (:product_id, :link, :notes)
        ");
        $stmt->bindValue(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->bindValue(':link',       $link,       PDO::PARAM_STR);
        $stmt->bindValue(':notes',      $notes,      PDO::PARAM_STR);
        $stmt->execute();

        redirect_to('index.php?tab=saved&view_product=' . $product_id . '&link_saved=1');
    } catch (PDOException $e) {
        redirect_to('index.php?tab=saved&view_product=' . $product_id . '&error=db');
    }
} else {
    redirect_to('index.php');
}
?>