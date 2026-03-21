<?php

require_once dirname(__DIR__)."/config/config.php";

session_start();
if(!isset($_SESSION['is_login'])){
    header("Location:".START_URL."auth/login.php");
    exit;
}

$id = $_SESSION['id_user'];
$username = $_SESSION['username'];

?>