<?php
require_once dirname(__DIR__) . '/config/config.php';
session_start();
$error = $_GET['error'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $code = $_POST['code'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($username === '' || strlen($username) < 3 || strlen($username) > 50 || $email === '' || $code === '' || strlen($password) < 8 || $password !== $confirm) {
        redirect_to('register.php?error=invalid_input');
    }

    try {

        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username LIMIT 1");
        $stmt->bindValue(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        if ($stmt->fetch()) {
            redirect_to('register.php?error=username_taken');
        }

        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        if ($stmt->fetch()) {
            redirect_to('register.php?error=email_taken');
        }

        $stmt = $pdo->prepare("SELECT id FROM users WHERE code = :code LIMIT 1");
        $stmt->bindValue(':code', $code, PDO::PARAM_STR);
        $stmt->execute();
        if ($stmt->fetch()) {
            redirect_to('register.php?error=code_taken');
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, code, password) VALUES (:username, :email, :code, :password_hash)");
        $stmt->bindValue(':username', $username, PDO::PARAM_STR);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':code', $code, PDO::PARAM_STR);
        $stmt->bindValue(':password_hash', $passwordHash, PDO::PARAM_STR);
        $stmt->execute();

        $_SESSION['is_login'] = true;
        $_SESSION['id_user'] = (int)$pdo->lastInsertId();
        $_SESSION['username'] = $username;


        redirect_to(BASE_URL. '?tab=test');
    } catch (PDOException $e) {
        redirect_to('register.php?error=db');
    }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>style.css">
</head>
<body>
<div class="login-card">
    <h2>Register</h2>

    <?php if ($error): ?>
        <div class="error">
            <?php
            $map = [
                'username_taken' => 'Username already exists.',
                'email_taken' => 'Email already exists.',
                'code_taken' => 'Code already exists.',
                'password_mismatch' => 'Passwords do not match.',
                'invalid_input' => 'Please check your input values.',
                'db' => 'Database error. Please try again.'
            ];
            echo htmlspecialchars($map[$error] ?? 'Registration error.');
            ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="register.php" class="login-sys-form">
        <label for="username">Username</label>
        <input id="username" name="username" required minlength="3" maxlength="50" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">

        <label for="email">Email</label>
        <input id="email" name="email" type="email" maxlength="255" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

        <label for="code">Code</label>
        <input id="code" name="code" maxlength="255" value="<?= htmlspecialchars($_POST['code'] ?? '') ?>">

        <label for="password">Password</label>
        <input id="password" name="password" type="password" required minlength="8">

        <label for="confirm_password">Confirm Password</label>
        <input id="confirm_password" name="confirm_password" type="password" required minlength="8">

        <button type="submit" class="button btn-primary">Create account</button>
    </form>

    <div class="links">
        <a href="login.php">Login</a>
        <span> | </span>
        <a href="forgot.php">Forgot password</a>
    </div>
</div>
</body>
</html>

