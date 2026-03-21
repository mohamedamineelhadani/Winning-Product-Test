<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/middleware/auth.php';

$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'test';

$stmt = $pdo->prepare("SELECT * FROM products WHERE id_user = :id_user ORDER BY created_at DESC");
$stmt->bindValue(':id_user', $id, PDO::PARAM_INT);
$stmt->execute();
$saved_products = $stmt->fetchAll(PDO::FETCH_ASSOC);


$product_details   = [];
$product_suppliers = [];
$product_profit    = null;
$product_links     = [];
$current_product   = null;

if (isset($_GET['view_product'])) {
    $product_id = (int)$_GET['view_product'];

    // Scores
    $stmt = $pdo->prepare("SELECT * FROM product_scores WHERE product_id = :product_id");
    $stmt->bindValue(':product_id', $product_id, PDO::PARAM_INT);
    $stmt->execute();
    $product_details = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Suppliers
    $stmt = $pdo->prepare("SELECT * FROM suppliers WHERE product_id = :product_id ORDER BY rating DESC");
    $stmt->bindValue(':product_id', $product_id, PDO::PARAM_INT);
    $stmt->execute();
    $product_suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Profit
    $stmt = $pdo->prepare("SELECT * FROM profit_calculations WHERE product_id = :product_id ORDER BY created_at DESC LIMIT 1");
    $stmt->bindValue(':product_id', $product_id, PDO::PARAM_INT);
    $stmt->execute();
    $product_profit = $stmt->fetch(PDO::FETCH_ASSOC);

    // Links
    $stmt = $pdo->prepare("SELECT * FROM product_links WHERE product_id = :product_id ORDER BY created_at DESC");
    $stmt->bindValue(':product_id', $product_id, PDO::PARAM_INT);
    $stmt->execute();
    $product_links = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Current product
    $product_stmt = $pdo->prepare("SELECT * FROM products WHERE id_user = :id_user AND id = :id");
    $product_stmt->bindValue(':id_user', $id, PDO::PARAM_INT);
    $product_stmt->bindValue(':id', $product_id, PDO::PARAM_INT);
    $product_stmt->execute();
    $current_product = $product_stmt->fetch(PDO::FETCH_ASSOC);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Winning Product Test</title>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>style.css">
</head>
<body>
    <div class="app-container">
        <header>
            <h1>Hello, <?php echo htmlspecialchars($username); ?> !</h1>
            <p>Evaluate your product against proven winning characteristics</p>
            <a href="<?= START_URL ?>auth/logout.php" class="button btn-secondary">Logout</a>
        </header>

        <?php if (isset($_GET['saved']) && $_GET['saved'] === 'true'): ?>
            <div class="alert alert-success">Product saved successfully!</div>
        <?php elseif (isset($_GET['supplier_saved'])): ?>
            <div class="alert alert-success">Supplier saved successfully!</div>
        <?php elseif (isset($_GET['profit_saved'])): ?>
            <div class="alert alert-success">Profit calculation saved!</div>
        <?php elseif (isset($_GET['link_saved'])): ?>
            <div class="alert alert-success">Link saved successfully!</div>
        <?php elseif (isset($_GET['error'])): ?>
            <div class="alert alert-error"><?php
                $errors = [
                    'file' => 'Error uploading file. Please try again.',
                    'size' => 'File is too large. Maximum size is 2MB.',
                    'type' => 'Invalid file type. Please upload an image (JPEG, PNG, GIF).',
                    'db'   => 'Database error. Please try again.'
                ];
                echo $errors[$_GET['error']] ?? 'An error occurred.';
            ?></div>
        <?php endif; ?>

        
        <div class="tabs">
            <div class="tab <?php echo $active_tab === 'test' ? 'active' : ''; ?>" data-tab="test">Test Product</div>
            <div class="tab <?php echo $active_tab === 'saved' ? 'active' : ''; ?>" data-tab="saved">Saved Products</div>
        </div>
        

        <div class="tab-content <?php echo $active_tab === 'test' ? 'active' : ''; ?>" id="test-tab">
            <h2>Test Your Product</h2>
            
            <form id="product-form" action="save-product.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="product-name">Product Name</label>
                    <input type="text" id="product-name" name="product_name" placeholder="Enter product name" required>
                </div>
                
                <div class="form-group">
                    <label for="product-description">Product Description</label>
                    <textarea id="product-description" name="product_description" rows="4" placeholder="Describe your product"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="product-image">Product Image</label>
                    <input type="file" id="product-image" name="product_image" accept="image/*">
                </div>
                
                <h3>Evaluate Characteristics</h3>
                <p>Rate your product for each characteristic (0-10 points):</p>
                
                <div id="characteristics">
                    <!-- Characteristics will be added here by JavaScript -->
                </div>
                
                <div class="results">
                    <h3>Test Results</h3>
                    <div class="total-score">Total Score: <span id="total-score">0</span>/140</div>
                    <div class="score-bar">
                        <div class="score-fill" id="score-visual" style="width: 0%"></div>
                    </div>
                    <p id="score-feedback"></p>
                </div>
                
                <div class="save-form">
                    <h3>Save Product</h3>
                    <p>If your product scored well, save it to your winning products database.</p>
                    <input type="hidden" name="total_score" id="save-total-score">
                    <div id="score-inputs">
                        <!-- Score inputs will be added here by JavaScript -->
                    </div>
                    <button type="submit" class="button btn-primary" name="save_product">Save Product</button>
                </div>
            </form>
        </div>
        
        <div class="tab-content <?php echo $active_tab === 'saved' ? 'active' : ''; ?>" id="saved-tab">
            <h2>Saved Winning Products</h2>
            <?php if (count($saved_products) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Score</th>
                            <th>Date Added</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($saved_products as $product): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($product['image'])): ?>
                                        <img src="<?= UPLOADS_URL.htmlspecialchars($product['image']) ?>" alt="Product Image" class="product-image">
                                    <?php else: ?>
                                        No Image
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><p class="p-description"><?php echo htmlspecialchars($product['description']); ?></p></td>
                                <td><?php echo $product['total_score']; ?>/140</td>
                                <td><?php echo date('M j, Y', strtotime($product['created_at'])); ?></td>
                                <td class="td-btns">
                                    <a href="?tab=saved&view_product=<?php echo $product['id']; ?>" class="button btn-primary">View Details</a>
                                    <a href="delete-product.php?id=<?php echo $product['id']; ?>" class="button btn-danger" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <?php if ($current_product): ?>

                    <div class="detail-header">
                        <?php if (!empty($current_product['image'])): ?>
                            <img src="<?= UPLOADS_URL . htmlspecialchars($current_product['image']) ?>" class="detail-image" alt="">
                        <?php endif; ?>
                        <div class="detail-info">
                            <h3><?= htmlspecialchars($current_product['name']) ?></h3>
                            <p><?= htmlspecialchars($current_product['description']) ?></p>
                            <span class="badge badge-lg badge-score"><?= $current_product['total_score'] ?>/140</span>
                        </div>
                    </div>

                    <nav class="sub-tabs">
                        <button class="sub-tab active" data-subtab="scores">Scores</button>
                        <button class="sub-tab" data-subtab="suppliers">Suppliers</button>
                        <button class="sub-tab" data-subtab="profit">Profit</button>
                        <button class="sub-tab" data-subtab="links">Links</button>
                    </nav>

                    <!-- scores -->
                    <div class="sub-tab-content active" id="subtab-scores">
                        <table>
                            <thead>
                                <tr>
                                    <th>Characteristic</th>
                                    <th>Score</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($product_details as $detail): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($detail['characteristic']); ?></td>
                                        <td><?php echo $detail['score']; ?>/10</td>
                                        <td><?php echo htmlspecialchars($detail['notes']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- supplires -->
                    <div class="sub-tab-content" id="subtab-suppliers">
                        <div class="sub-section-header">
                            <h4>Suppliers</h4>
                            <button class="button btn-primary" onclick="toggleForm('supplier-form')">+ Add Supplier</button>
                        </div>

                        <div class="inline-form" id="supplier-form" style="display:none">
                            <form action="save-supplier.php" method="POST">
                                <input type="hidden" name="product_id" value="<?= $current_product['id'] ?>">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Supplier Name *</label>
                                        <input type="text" name="name" required placeholder="e.g. AliExpress Store">
                                    </div>
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" name="email" placeholder="supplier@email.com">
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Phone</label>
                                        <input type="text" name="phone" placeholder="+212 555000000">
                                    </div>
                                    <div class="form-group">
                                        <label>Store URL</label>
                                        <input type="url" name="url" placeholder="https://aliexpress.com/…">
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Rating (0-5)</label>
                                        <input type="number" name="rating" min="0" max="5" value="0">
                                    </div>
                                    <div class="form-group">
                                        <label>Shipping Days *</label>
                                        <input type="number" name="shipping_days" min="0" step="0.5" value="7" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Notes</label>
                                    <textarea name="notes" rows="2" placeholder="Any notes about this supplier…"></textarea>
                                </div>
                                <div class="form-actions">
                                    <button type="submit" class="button btn-primary">Save Supplier</button>
                                    <button type="button" class="button btn-ghost" onclick="toggleForm('supplier-form')">Cancel</button>
                                </div>
                            </form>
                        </div>

                        <?php if (count($product_suppliers) > 0): ?>
                        <div class="suppliers-list">
                            <?php foreach ($product_suppliers as $s): ?>
                            <div class="supplier-card">
                                <div class="supplier-card__head">
                                    <strong><?= htmlspecialchars($s['name']) ?></strong>
                                    <span class="stars"><?= str_repeat('★', $s['rating']) ?><?= str_repeat('☆', 5 - $s['rating']) ?></span>
                                </div>
                                <div class="supplier-card__meta">
                                    <?php if ($s['email']): ?><span>✉ <?= htmlspecialchars($s['email']) ?></span><?php endif; ?>
                                    <?php if ($s['phone']): ?><span>☎ <?= htmlspecialchars($s['phone']) ?></span><?php endif; ?>
                                    <span>☯ <?= $s['shipping_days'] ?> days</span>
                                    <?php if ($s['url']): ?><a href="<?= htmlspecialchars($s['url']) ?>" target="_blank" class="link-btn">Visit Store ↗</a><?php endif; ?>
                                </div>
                                <?php if ($s['notes']): ?><p class="supplier-notes"><?= htmlspecialchars($s['notes']) ?></p><?php endif; ?>
                                <a href="delete-supplier.php?id=<?= $s['id'] ?>&product_id=<?= $current_product['id'] ?>" class="button btn-danger" onclick="return confirm('Delete supplier?')">Remove</a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                            <p class="empty-state">No suppliers added yet.</p>
                        <?php endif; ?>
                    </div>

                    <!-- ── PROFIT ── -->
                    <div class="sub-tab-content" id="subtab-profit">
                        <div class="sub-section-header">
                            <h4>Profit Calculator</h4>
                            <button class="button btn-primary btn-sm" onclick="toggleForm('profit-form')">✏ Edit</button>
                        </div>

                        <div class="inline-form" id="profit-form" <?= !$product_profit ? '' : 'style="display:none"' ?>>
                            <form action="save-profit.php" method="POST">
                                <input type="hidden" name="product_id" value="<?= $current_product['id'] ?>">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Cost Price ($)</label>
                                        <input type="number" name="cost_price" step="0.01" min="0" value="<?= $product_profit['cost_price'] ?? 0 ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Shipping Cost ($)</label>
                                        <input type="number" name="shipping_cost" step="0.01" min="0" value="<?= $product_profit['shipping_cost'] ?? 0 ?>">
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Ad Cost ($)</label>
                                        <input type="number" name="ad_cost" step="0.01" min="0" value="<?= $product_profit['ad_cost'] ?? 0 ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>Other Costs ($)</label>
                                        <input type="number" name="other_costs" step="0.01" min="0" value="<?= $product_profit['other_costs'] ?? 0 ?>">
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Selling Price ($)</label>
                                        <input type="number" name="selling_price" step="0.01" min="0" value="<?= $product_profit['selling_price'] ?? 0 ?>" required>
                                    </div>
                                </div>
                                <div class="form-actions">
                                    <button type="submit" class="button btn-primary">Calculate & Save</button>
                                    <?php if ($product_profit): ?><button type="button" class="button btn-ghost" onclick="toggleForm('profit-form')">Cancel</button><?php endif; ?>
                                </div>
                            </form>
                        </div>

                        <?php if ($product_profit): ?>
                        <div class="profit-dashboard">
                            <div class="profit-grid">
                                <div class="profit-tile">
                                    <span class="profit-tile__label">Cost Price</span>
                                    <span class="profit-tile__value"><?= number_format($product_profit['cost_price'], 2) ?> DH</span>
                                </div>
                                <div class="profit-tile">
                                    <span class="profit-tile__label">Shipping</span>
                                    <span class="profit-tile__value"><?= number_format($product_profit['shipping_cost'], 2) ?> DH</span>
                                </div>
                                <div class="profit-tile">
                                    <span class="profit-tile__label">Ad Cost</span>
                                    <span class="profit-tile__value"><?= number_format($product_profit['ad_cost'], 2) ?> DH</span>
                                </div>
                                <div class="profit-tile">
                                    <span class="profit-tile__label">Other Costs</span>
                                    <span class="profit-tile__value"><?= number_format($product_profit['other_costs'], 2) ?> DH</span>
                                </div>
                                <div class="profit-tile">
                                    <span class="profit-tile__label">Selling Price</span>
                                    <span class="profit-tile__value"><?= number_format($product_profit['selling_price'], 2) ?> DH</span>
                                </div>
                                <div class="profit-tile profit-tile--gross">
                                    <span class="profit-tile__label">Gross Profit</span>
                                    <span class="profit-tile__value"><?= number_format($product_profit['gross_profit'], 2) ?> DH</span>
                                </div>
                                <div class="profit-tile profit-tile--net <?= $product_profit['net_profit'] >= 0 ? 'profit-tile--pos' : 'profit-tile--neg' ?>">
                                    <span class="profit-tile__label">Net Profit</span>
                                    <span class="profit-tile__value"><?= number_format($product_profit['net_profit'], 2) ?> DH</span>
                                </div>
                                <div class="profit-tile">
                                    <span class="profit-tile__label">Gross Margin</span>
                                    <span class="profit-tile__value"><?= $product_profit['gross_margin_percent'] ?>%</span>
                                </div>
                                <div class="profit-tile profit-tile--roi">
                                    <span class="profit-tile__label">ROI</span>
                                    <span class="profit-tile__value"><?= $product_profit['roi_percent'] ?>%</span>
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                            <p class="empty-state">No profit data yet. Fill in the form above.</p>
                        <?php endif; ?>
                    </div>

                    <!-- ── LINKS ── -->
                    <div class="sub-tab-content" id="subtab-links">
                        <div class="sub-section-header">
                            <h4>Product Links</h4>
                            <button class="button btn-primary" onclick="toggleForm('links-form')">+ Add Link</button>
                        </div>

                        <div class="inline-form" id="links-form" style="display:none">
                            <form action="save-link.php" method="POST">
                                <input type="hidden" name="product_id" value="<?= $current_product['id'] ?>">
                                <div class="form-group">
                                    <label>URL *</label>
                                    <input type="url" name="link" required placeholder="https://aliexpress.com/item/…">
                                </div>
                                <div class="form-group">
                                    <label>Notes</label>
                                    <textarea name="notes" rows="2" placeholder="Competitor listing, supplier page, ad reference…"></textarea>
                                </div>
                                <div class="form-actions">
                                    <button type="submit" class="button btn-primary">Save Link</button>
                                    <button type="button" class="button btn-ghost" onclick="toggleForm('links-form')">Cancel</button>
                                </div>
                            </form>
                        </div>

                        <?php if (count($product_links) > 0): ?>
                        <div class="links-list">
                            <?php foreach ($product_links as $l): ?>
                            <div class="link-item">
                                <div class="link-item__body">
                                    <a href="<?= htmlspecialchars($l['link']) ?>" target="_blank" class="link-url">Visit Store ↗</a>
                                    <?php if ($l['notes']): ?><p class="link-notes"><?= htmlspecialchars($l['notes']) ?></p><?php endif; ?>
                                    <span class="date"><?= date('M j, Y', strtotime($l['created_at'])) ?></span>
                                </div>
                                <a href="delete-link.php?id=<?= $l['id'] ?>&product_id=<?= $current_product['id'] ?>" class="button btn-danger btn-xs" onclick="return confirm('Delete link?')">✕</a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                            <p class="empty-state">No links added yet.</p>
                        <?php endif; ?>
                    </div>

                <?php endif; ?>
            <?php else: ?>
                <p>No products saved yet. Test a product and save it to see it here.</p>
            <?php endif; ?>
            
            <div class="export-buttons">
                <a href="export.php?format=json" class="button btn-primary">Export as JSON</a>
                <a href="export.php?format=csv" class="button btn-secondary">Export as CSV</a>
            </div>
        </div>
    </div>
    <script src="<?= ASSETS_URL ?>script.js"></script>
</body>
</html>