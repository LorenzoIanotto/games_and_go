<?php
require_once "../../../lib/auth.php";
require_once "../../../lib/db_actions/user.php";
session_start();
protect_page(UserRole::Admin);

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    die("Metodo non supportato");
}

$user_id = $_POST["user_id"];

if (!isset($user_id)) {
    die("Richiesta malformata");
}

$res = delete_user($user_id);

header("Location: /site/admin/users/".($res ? "" : "?delete_error=true"));
?>
