<?php
require_once (__DIR__ . "/product.php");

function insert_employee_order(int $employee_id, int $vendor_id, string $total_amount, array $products_with_quantity): bool {
    if (sizeof($products_with_quantity) == 0) {
        return false;
    }

    $conn = DatabaseConnection::get_instance();
    $conn->begin_transaction();
    $stmt = $conn->prepare("INSERT INTO InternalOrder (employee_id, vendor_id, total_amount) VALUES (?, ?, ?)");
    $stmt->bind_param("iid", $employee_id, $vendor_id, $total_amount);
    
    if (!$stmt->execute()) {
        $conn->rollback();
        return false;
    }

    $order_id = $conn->insert_id;
    foreach ($products_with_quantity as $product_id => $quantity) {
        $stmt = $conn->prepare("INSERT INTO InternalOrderProduct (order_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $order_id, $product_id, $quantity);
        $res = $stmt->execute();

        if (!$res) {
            $conn->rollback();
            return false;
        }

        $old_quantity = get_product_quantity($product_id);

        if (!$old_quantity) {
            $conn->rollback();
            return false;
        }

        $new_quantity = $old_quantity + $quantity;

        $stmt = $conn->prepare("UPDATE Product SET quantity=? WHERE id=?");
        $stmt->bind_param("ii", $new_quantity, $product_id);
        $res = $stmt->execute();

        if (!$res) {
            $conn->rollback();
            return false;
        }
    }

    $conn->commit();
    return true;
}
?>
