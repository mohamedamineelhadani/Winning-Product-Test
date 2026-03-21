<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/middleware/auth.php';

if (isset($_GET['id'], $_GET['product_id'])) {
    $supplier_id = (int)$_GET['id'];
    $product_id  = (int)$_GET['product_id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM suppliers WHERE id = :id");
        $stmt->bindValue(':id', $supplier_id, PDO::PARAM_INT);
        $stmt->execute();
        redirect_to('index.php?tab=saved&view_product=' . $product_id);

    } catch (PDOException $e) {
        redirect_to('index.php?tab=saved&view_product=' . $product_id . '&error=db');
    }
} else {
    redirect_to('index.php');
}
?>