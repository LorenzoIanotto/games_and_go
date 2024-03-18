<?php
require_once "../../../../../lib/auth.php";
require_once "../../../../../lib/db_actions/customer.php";
session_start();
protect_page(UserRole::Customer);

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    die("Metodo non supportato");
}

$summary = $_POST["summary"] ?? null;
$description = $_POST["description"] ?? null;
$rating = $_POST["rating"] ?? null;
$product_id = $_POST["product_id"] ?? null;

if (!(
    $summary &&
    $description &&
    is_numeric($product_id) &&
    is_numeric($rating) &&
    $rating >= 0 &&
    $rating <= 5
)) {
    die("Richiesta malformata");
}

$res = insert_user_feedback($summary, $description, $rating, get_user_id(), $product_id);

if (!$res) die("Errore");

header("Location: /site/customer/products/details/?product_id=".$product_id);
