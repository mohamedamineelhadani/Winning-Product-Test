<?php
require_once dirname(__DIR__)."/config/config.php";
session_start();
$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $code = $_POST['code'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($email === '' || $code === '' || $password === '') {
        redirect_to('forgot.php?error=invalid');
    }

    if (strlen($password) < 8) {
        redirect_to('forgot.php?error=password_err');
    }
    
    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email AND code = :code LIMIT 1");
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':code', $code, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch();
        if ($user) {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :user_id");
            $stmt->bindValue(':password', $passwordHash, PDO::PARAM_STR);
            $stmt->bindValue(':user_id', $user['id'], PDO::PARAM_INT);
            $stmt->execute();
            redirect_to('forgot.php?success=success');
        }else {
            redirect_to('forgot.php?error=error');
        }
    }catch (PDOException $e) {
        redirect_to('forgot.php?error=db');
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot</title>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>style.css">
</head>
<body>
<div class="login-card">
    <h2>Forgot password</h2>
    
    <?php
    $map = [
        'invalid' => 'All fields are required.',
        'password_err' => 'Password error.',
        'error' => 'Code or Email is wrong.',
        'db' => 'Database error. Please try again.'
    ];
    ?>

    <?php if ($error !== ''): ?>
        <div class="error">
            <?= htmlspecialchars($map[$error] ?? 'Error occurred.') ?>
        </div>
    <?php endif; ?>

    <?php if ($success === 'success'): ?>
        <div class="success"><?= "Password reset successfully." ?></div>
    <?php endif; ?>


    <form method="POST" action="<?= $_SERVER['PHP_SELF'] ?>" class="login-sys-form">
        <label for="email">Email</label>
        <input id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        <label for="code">Code</label>
        <input id="code" name="code" required value="<?= htmlspecialchars($_POST['code'] ?? '') ?>">
        <label for="password">New Password</label>
        <input id="password" name="password" type="password" required value="<?= htmlspecialchars($_POST['password'] ?? '') ?>">
        <button class="button btn-primary" type="submit">Get Password</button>
    </form>

    <div class="links">
        <a href="register.php">Register</a>
        <span> | </span>
        <a href="login.php">Login</a>
    </div>
</div>
</body>
</html>

