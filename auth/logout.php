<?php
require_once dirname(__DIR__)."/config/config.php";
session_start();
session_unset();
$_SESSION = [];
session_destroy();
redirect_to(START_URL."auth/login.php");
?>