<?php
enum PaymentMethod: string {
    case Bancomat = "bancomat";
    case CreditCard = "credit_card";
    case CashOnDelivery = "cash_on_delivery";
    case BankTransfer = "bank_transfer";
}

function insert_customer_order(int $customer_id, PaymentMethod $payment_method, string $payment_method_code, int $address_id, array $products_with_quantity) {
    $payment_method_value = $payment_method->value;
    $total_amount = get_total_amount($products_with_quantity);

    $conn = DatabaseConnection::get_instance();
    $conn->begin_transaction();
    $stmt = $conn->prepare("INSERT INTO CustomerOrder (customer_id, payment_method, payment_method_code, total_amount, order_status, address_id)
                    VALUES (?, ?, ?, ?, 'packaging_in_progress', ?)");
    $stmt->bind_param("issdi", $customer_id, $payment_method_value, $payment_method_code, $total_amount, $address_id);
    $stmt->execute();

    $order_id = $conn->insert_id;
    foreach ($products_with_quantity as $product_id => $quantity) {
        $stmt = $conn->prepare("INSERT INTO CustomerOrderProduct (order_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $order_id, $product_id, $quantity);
        $res = $stmt->execute();

        if (!$res) {
            $conn->rollback();
            return false;
        }
    }

    $conn->commit();
    return true;
}

// Decimal values are handled as strings to avoid precison errors
function get_total_amount(array $products_with_quantity): string|null {
    $conn = DatabaseConnection::get_instance();

    $ids = array_keys($products_with_quantity);
    $placeholders = implode(',', array_fill(0, count($ids), '?')); // Es. ?,?,?, ...
    $param_types = str_repeat('i', count($ids)); // Es. iii

    $stmt = $conn->prepare("SELECT SUM(price) AS total_amount FROM Product WHERE id IN ($placeholders)");
    $stmt->bind_param($param_types, ...$ids);
    $stmt->execute();
    $res = $stmt->get_result();

    if (!$res) return null;

    return $res->fetch_assoc()["total_amount"];
}