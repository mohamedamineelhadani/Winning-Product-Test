<?php
define("ROOT",dirname(__DIR__));

define("IS_LOCALHOST", in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1']));
define("SSL", !IS_LOCALHOST);
define("DOMAIN", $_SERVER['HTTP_HOST']);

define("FILE_NAME", IS_LOCALHOST ? basename(ROOT) : "");

define("DEBUG", true);
ini_set("display_errors", DEBUG ? 1 : 0);
error_reporting(DEBUG ? E_ALL : 0);

$protocol = SSL ? "https" : "http";
$basePath = IS_LOCALHOST ? "/" . FILE_NAME : "";

define("START_URL", "$protocol://" . DOMAIN . $basePath . "/");
define("BASE_URL", "$protocol://" . DOMAIN . $basePath . "/app/index.php");
define("ASSETS_URL", "$protocol://" . DOMAIN . $basePath . "/assets/");
define("UPLOADS_URL", "$protocol://" . DOMAIN . $basePath . "/uploads/");

define("UPLOAD_DIR", ROOT . "/uploads/");
define("ASSETS_DIR", ROOT . "/assets/");
define('MAX_FILE_SIZE', 2 * 1024 * 1024);
define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/gif']);

define("APP_NAME", "Winning Products");
define("ADMIN_NAME", "Mohamed Amine El Hadani");
define("ADMIN_EMAIL", "elhadanimohamedamine@gmail.com");

define('DB_HOST', 'localhost');
define('DB_NAME', 'winning_products');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true);
}


function redirect_to($url) {
    header("Location: $url");
    exit;
}
?>