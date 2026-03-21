<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/middleware/auth.php';

if (isset($_GET['format'])) {
    $format = $_GET['format'];

    // Products + scores
    $stmt = $pdo->prepare("
        SELECT p.*, ps.characteristic, ps.score AS char_score, ps.notes AS char_notes
        FROM products p
        LEFT JOIN product_scores ps ON p.id = ps.product_id
        WHERE p.id_user = :id_user
        ORDER BY p.created_at DESC, p.id
    ");
    $stmt->bindValue(':id_user', $id, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $products = [];
    foreach ($rows as $row) {
        $pid = $row['id'];
        if (!isset($products[$pid])) {
            $products[$pid] = [
                'id'          => $pid,
                'name'        => $row['name'],
                'description' => $row['description'],
                'total_score' => $row['total_score'],
                'created_at'  => $row['created_at'],
                'scores'      => [],
                'suppliers'   => [],
                'profit'      => null,
                'links'       => [],
            ];
        }
        if ($row['characteristic']) {
            $products[$pid]['scores'][] = [
                'characteristic' => $row['characteristic'],
                'score'          => $row['char_score'],
                'notes'          => $row['char_notes'],
            ];
        }
    }

    // Attach suppliers
    $stmt = $pdo->prepare("
        SELECT s.* FROM suppliers s
        JOIN products p ON s.product_id = p.id
        WHERE p.id_user = :id_user
    ");
    $stmt->bindValue(':id_user', $id, PDO::PARAM_INT);
    $stmt->execute();
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $s) {
        if (isset($products[$s['product_id']])) {
            $products[$s['product_id']]['suppliers'][] = $s;
        }
    }

    // Attach profit
    $stmt = $pdo->prepare("
        SELECT pc.* FROM profit_calculations pc
        JOIN products p ON pc.product_id = p.id
        WHERE p.id_user = :id_user
    ");
    $stmt->bindValue(':id_user', $id, PDO::PARAM_INT);
    $stmt->execute();
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $pc) {
        if (isset($products[$pc['product_id']])) {
            $products[$pc['product_id']]['profit'] = $pc;
        }
    }

    // Attach links
    $stmt = $pdo->prepare("
        SELECT pl.* FROM product_links pl
        JOIN products p ON pl.product_id = p.id
        WHERE p.id_user = :id_user
    ");
    $stmt->bindValue(':id_user', $id, PDO::PARAM_INT);
    $stmt->execute();
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $pl) {
        if (isset($products[$pl['product_id']])) {
            $products[$pl['product_id']]['links'][] = $pl;
        }
    }

    if ($format === 'json') {
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="winning-products.json"');
        echo json_encode(array_values($products), JSON_PRETTY_PRINT);

    } elseif ($format === 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="winning-products.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Product Name', 'Description', 'Total Score', 'Date Added', 'Net Profit ($)', 'ROI (%)', 'Suppliers Count', 'Links Count']);
        foreach ($products as $p) {
            fputcsv($out, [
                $p['name'],
                $p['description'],
                $p['total_score'],
                $p['created_at'],
                $p['profit']['net_profit'] ?? '',
                $p['profit']['roi_percent'] ?? '',
                count($p['suppliers']),
                count($p['links']),
            ]);
        }
        fclose($out);
    }

    exit;
} else {
    redirect_to('index.php');
}
?>