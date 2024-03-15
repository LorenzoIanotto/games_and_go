<?php
require_once "../../lib/auth.php";

session_start();
protect_page(UserRole::Customer);

die("SONO PASSATO");
?>
