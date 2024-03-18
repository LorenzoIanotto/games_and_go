<?php
require_once "../../../../../lib/auth.php";
require_once "../../../../../lib/db_actions/customer.php";
session_start();
protect_page(UserRole::Customer);

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    die("Metodo non supportato");
}

$product_id = $_POST["product_id"] ?? null;

if (!is_numeric($product_id)) {
    die("Richiesta malformata");
}

$res = delete_user_feedback(get_user_id(), $product_id);

if (!$res) die("Errore");

header("Location: /site/customer/products/details/?product_id=".$product_id);
?>
