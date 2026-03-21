<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/middleware/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id    = (int)$_POST['product_id'];
    $cost_price    = max(0, (float)$_POST['cost_price']);
    $shipping_cost = max(0, (float)($_POST['shipping_cost'] ?? 0));
    $other_costs   = max(0, (float)($_POST['other_costs']  ?? 0));
    $ad_cost       = max(0, (float)($_POST['ad_cost']      ?? 0));
    $selling_price = max(0, (float)$_POST['selling_price']);

    $total_costs        = $cost_price + $shipping_cost + $other_costs + $ad_cost;
    $gross_profit       = $selling_price - $cost_price - $shipping_cost - $other_costs;
    $net_profit         = $selling_price - $total_costs;
    $gross_margin_pct   = $selling_price > 0 ? round(($gross_profit / $selling_price) * 100, 2) : null;
    $roi_pct            = $total_costs  > 0 ? round(($net_profit  / $total_costs)   * 100, 2) : null;

    try {
        $del = $pdo->prepare("DELETE FROM profit_calculations WHERE product_id = :product_id");
        $del->bindValue(':product_id', $product_id, PDO::PARAM_INT);
        $del->execute();

        $stmt = $pdo->prepare("
            INSERT INTO profit_calculations
                (product_id, cost_price, shipping_cost, other_costs, ad_cost, selling_price,
                 gross_profit, gross_margin_percent, net_profit, roi_percent)
            VALUES
                (:product_id, :cost_price, :shipping_cost, :other_costs, :ad_cost, :selling_price,
                 :gross_profit, :gross_margin_percent, :net_profit, :roi_percent)
        ");
        $stmt->bindValue(':product_id',           $product_id,      PDO::PARAM_INT);
        $stmt->bindValue(':cost_price',            $cost_price);
        $stmt->bindValue(':shipping_cost',         $shipping_cost);
        $stmt->bindValue(':other_costs',           $other_costs);
        $stmt->bindValue(':ad_cost',               $ad_cost);
        $stmt->bindValue(':selling_price',         $selling_price);
        $stmt->bindValue(':gross_profit',          $gross_profit);
        $stmt->bindValue(':gross_margin_percent',  $gross_margin_pct);
        $stmt->bindValue(':net_profit',            $net_profit);
        $stmt->bindValue(':roi_percent',           $roi_pct);
        $stmt->execute();

        redirect_to('index.php?tab=saved&view_product=' . $product_id . '&profit_saved=1');
    } catch (PDOException $e) {
        redirect_to('index.php?tab=saved&view_product=' . $product_id . '&error=db');
    }
} else {
    redirect_to('index.php');
}
?>