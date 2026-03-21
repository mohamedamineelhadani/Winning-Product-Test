<?php
require_once dirname(__DIR__) . '/config/config.php';
session_start();
$error = $_GET['error'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        redirect_to('login.php?error=invalid');
    }


    try {
        $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE email = :email LIMIT 1");
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            redirect_to('login.php?error=invalid');
        }

        $_SESSION['is_login'] = true;
        $_SESSION['id_user'] = (int)$user['id'];
        $_SESSION['username'] = $user['username'];

        redirect_to(BASE_URL. '?tab=test');
    } catch (PDOException $e) {
        redirect_to('login.php?error=db');
    }

}


?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>style.css">
</head>
<body>
<div class="login-card">
    <h2>Login</h2>
    <?php if ($error): ?>
        <div class="error">
            <?php
            $map = [
                'invalid' => 'Invalid email or password.',
                'db' => 'Database error. Please try again.'
            ];
            echo htmlspecialchars($map[$error] ?? 'Login error.');
            ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= $_SERVER['PHP_SELF'] ?>" class="login-sys-form">
        <label for="email">Email</label>
        <input id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

        <label for="password">Password</label>
        <input id="password" name="password" type="password" required value="<?= htmlspecialchars($_POST['password'] ?? '') ?>">

        <button class="button btn-primary" type="submit">Login</button>
    </form>

    <div class="links">
        <a href="register.php">Register</a>
        <span> | </span>
        <a href="forgot.php">Forgot password</a>
    </div>
</div>
</body>
</html>

