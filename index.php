<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/middleware/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>style.css">
</head>
<body>
    <div class="welcome-container">
        <h1 class="welcome-title">Winning Product Test</h1>
        <p class="welcome-subtitle">Evaluate your product against proven winning characteristics</p>
        <?php if (isset($_SESSION['is_login']) && $_SESSION['is_login'] === true): ?>
                <div class="welcome-btn">
                    <p class="welcome-user">Logged in as <strong><?php echo htmlspecialchars($username); ?></strong></p>
                    <a href="<?= BASE_URL ?>" class="button btn-primary">App</a>
                </div>
            <?php else: ?>
                <div class="welcome-btn">
                    <a href="<?= START_URL ?>auth/login.php" class="button btn-primary" style="margin-right:10px;">Login</a>
                    <a href="<?= START_URL ?>auth/register.php" class="button btn-secondary">Register</a>
                </div>
            <?php endif; ?>
    </div>
</body>
</html>