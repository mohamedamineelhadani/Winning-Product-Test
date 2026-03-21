<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/middleware/auth.php';

if (isset($_GET['id'])) {
    $product_id = (int)$_GET['id'];
    
    try {
        $stmt = $pdo->prepare("SELECT image FROM products WHERE id = :id");
        $stmt->bindValue(':id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
        $stmt->bindValue(':id', $product_id, PDO::PARAM_INT);
        $stmt->execute();

        if ($product && $product['image'] && file_exists(UPLOAD_DIR . $product['image'])) {
            unlink(UPLOAD_DIR . $product['image']);
        }

        redirect_to('index.php?tab=saved');
    } catch (PDOException $e) {
        redirect_to('index.php?tab=saved&error=db');
    }
} else {
    redirect_to('index.php?tab=saved');
}
?>