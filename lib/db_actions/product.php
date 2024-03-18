<?php
require_once (__DIR__ . "/../db.php");

function get_product_quantity(int $product_id): int|null {

    $conn = DatabaseConnection::get_instance();
    $stmt = $conn->prepare("SELECT quantity FROM Product WHERE id=?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if (!$res) return null;

    $row = $res->fetch_assoc();

    if (!$row) return null;

    return intval($row["quantity"]);
}
?>
