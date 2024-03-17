<?php
require_once "product.php";

enum PaymentMethod: string {
    case Bancomat = "bancomat";
    case CreditCard = "credit_card";
    case CashOnDelivery = "cash_on_delivery";
    case BankTransfer = "bank_transfer";
}

class QuantityTooLargeError {
    private $product_id;

    public function __construct(int $product_id) {
        $this->product_id = $product_id;
    }

    public function product_id(): int {
        return $this->product_id;
    }
}

enum InsertCustomerOrderError {
    case NoProducts;
    case DatabaseError;
}

function insert_customer_order(
    int $customer_id,
    PaymentMethod $payment_method,
    string $payment_method_code,
    int $address_id,
    string $total_amount,
    array $products_with_quantity
): InsertCustomerOrderError|QuantityTooLargeError|null {
    if (sizeof($products_with_quantity) == 0) {
        return InsertCustomerOrderError::NoProducts;
    }

    $payment_method_value = $payment_method->value;

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
            return InsertCustomerOrderError::DatabaseError;
        }

        $old_quantity = get_product_quantity($product_id);

        if (!$old_quantity) {
            $conn->rollback();
            return InsertCustomerOrderError::DatabaseError;
        }

        $new_quantity = $old_quantity - $quantity;

        if ($new_quantity < 0) {
            $conn->rollback();
            return new QuantityTooLargeError($product_id);
        }

        $stmt = $conn->prepare("UPDATE Product SET quantity=? WHERE id=?");
        $stmt->bind_param("ii", $new_quantity, $product_id);
        $res = $stmt->execute();

        if (!$res) {
            $conn->rollback();
            return InsertCustomerOrderError::DatabaseError;
        }
    }

    $conn->commit();
    return null;
}

// Decimal values are handled as strings to avoid precison errors
// All price manipulations (apart from displaying) are done by the DB
function get_total_amount(array $products_with_quantity, PaymentMethod|null $payment_method = null): string|null {
    if (sizeof($products_with_quantity) == 0) return null;

    $conn = DatabaseConnection::get_instance();
    $prices = [];

    foreach ($products_with_quantity as $product_id => $quantity) {
        $quantity_int = intval($quantity);
        $stmt = $conn->prepare("SELECT price*$quantity_int AS price FROM Product WHERE id=?");
        $stmt->bind_param("i", $product_id);
        if (!$stmt->execute()) return null;

        $price = $stmt->get_result()->fetch_assoc()["price"];
        $prices[] = $price;
    }

    $sum_string_representation = implode("+", $prices);
    if ($payment_method === PaymentMethod::CashOnDelivery) {
        $sum_string_representation .= "+10.00";
    }
    $res = $conn->query("SELECT $sum_string_representation AS sum");

    if (!$res) return null;

    $total_amount = $res->fetch_assoc()["sum"];

    return $total_amount;
}
