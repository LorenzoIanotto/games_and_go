<?php
session_start();

require_once "../lib/auth.php";
logout();
header("Location: /site/login");
?>
