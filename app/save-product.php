<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/middleware/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['product_name'];
    $description = $_POST['product_description'];
    $total_score = (int)$_POST['total_score'];
    $scores = $_POST['scores'];
    
    $image = null;
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['product_image'];
        
        // Check file size
        if ($file['size'] > MAX_FILE_SIZE) {
            redirect_to('index.php?error=size');
        }
        
        // Check file type
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime_type = $finfo->file($file['tmp_name']);
        if (!in_array($mime_type, ALLOWED_TYPES)) {
            redirect_to('index.php?error=type');
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $destination = UPLOAD_DIR . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            $image = $filename;
        } else {
            redirect_to('index.php?error=file');
        }
    }
    
    try {
        // Insert product
        $stmt = $pdo->prepare("INSERT INTO products (id_user, name, description, image, total_score) VALUES (:id_user, :name, :description, :image, :total_score)");
        $stmt->bindValue(':id_user', $id, PDO::PARAM_INT);
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->bindValue(':description', $description, PDO::PARAM_STR);
        $stmt->bindValue(':image', $image, PDO::PARAM_STR);
        $stmt->bindValue(':total_score', $total_score, PDO::PARAM_INT);
        $stmt->execute();
        
        $product_id = $pdo->lastInsertId();
        
        // Insert scores
        foreach ($scores as $characteristic => $score_data) {
            $score = (int)$score_data['score'];
            $notes = $score_data['notes'] ?? '';
            
            $stmt = $pdo->prepare("INSERT INTO product_scores (product_id, characteristic, score, notes) VALUES (:product_id, :characteristic, :score, :notes)");
            $stmt->bindValue(':product_id', $product_id, PDO::PARAM_INT);
            $stmt->bindValue(':characteristic', $characteristic, PDO::PARAM_STR);
            $stmt->bindValue(':score', $score, PDO::PARAM_INT);
            $stmt->bindValue(':notes', $notes, PDO::PARAM_STR);
            $stmt->execute();
        }
        
        redirect_to('index.php?saved=true');
    } catch (PDOException $e) {
        redirect_to('index.php?error=db');
    }
} else {
    redirect_to('index.php');
}
?>