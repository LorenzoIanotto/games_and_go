<?php
require_once "../../../../lib/auth.php";
require_once "../../../../lib/db.php";
require_once "../../../../lib/db_actions/order.php";
require_once "../../../../lib/db_actions/customer.php";
session_start();
protect_page(UserRole::Customer);

$customer_id = get_user_id();
$products_with_quantity = $_SESSION["products_with_quantity"] ?? [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $payment_method = PaymentMethod::tryFrom($_POST["payment_method"]);
    $payment_method_code = $_POST["payment_method_code"];
    $address_id = intval($_POST["address_id"]);

    if (!($payment_method && $payment_method_code && $address_id)) {
        die("Richiesta malformata");
    }

    $total_amount = get_total_amount($products_with_quantity, $payment_method);

    $err = insert_customer_order($customer_id, $payment_method, $payment_method_code, $address_id, $total_amount, $products_with_quantity);
    
    if ($err == null) {
        $_SESSION["products_with_quantity"] = [];
        add_points_to_customer($customer_id, 5);
    } else if ($err instanceof QuantityTooLargeError) {
        header("Location: /site/customer/cart/?invalid_quantity_product_id=".$err->product_id());
        die();
    }

    header("Location: /site/customer/");
    die();
}

$conn = DatabaseConnection::get_instance();
$stmt = $conn->prepare("SELECT id, extension, house_number, street, city, postcode, country_code FROM Address
                        JOIN CustomerAddress ON address_id=id WHERE customer_id=?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$addresses_res = $stmt->get_result();

function address_to_string(array $address) {
    $str = "";

    foreach ($address as $key => $value) {
        if ($key != "id" && $value != "") $str = $str.$value.", ";
    }

    $str = implode(", ", array_filter($address, function ($v, $k) { return $k != "id" && $v != ""; }, ARRAY_FILTER_USE_BOTH));

    return $str;
}

// Always display the net total
$total_amount = get_total_amount($products_with_quantity);

?>
<!DOCTYPE html>
<html>
    <head>
        <?php require "../../../../components/bootstrap.php" ?>
        <title>Effettua ordine</title>
    </head>
    <body>
        <?php require "../../../../components/headers/customer.php" ?>
        <main class="container">
            <div class="card">
                <span class="card-header">Totale (netto): <strong>€<?php echo number_format($total_amount, 2)?></strong></span>
                <form method="POST" class="card-body">
                    <div class="row row-cols-2 g-3">
                        <div class="col">
                            <?php
                            if ($addresses_res->num_rows > 0) {
                                echo '<label class="form-label" for="address-id-input">Indirizzo</label>';
                                echo '<select class="form-control form-select" name="address_id" id="address-id-input" required>';
                                while ($row = $addresses_res->fetch_assoc()) {
                                    echo '"<option value="'.$row["id"].'">'.address_to_string($row)."</option>";
                                }
                                echo '</select>';
                            }
                            ?>
                            <a class="form-text" href="/site/customer/add_address/">Devi aggiungere un indirizzo?</a>
                        </div>
                        <div class="col">
                            <label class="form-label" for="payment-method-input">Tipo di pagamento</label>
                            <select class="form-control form-select" name="payment_method" id="payment-method-input" required>
                                <option value="bancomat">Bancomat</option>
                                <option value="credit_card">Carta di credito</option>
                                <option value="cash_on_delivery">Contrassegno (+€10)</option>
                                <option value="bank_transfer">Bonifico bancario</option>
                            </select>
                        </div>
                        <div class="col">
                            <label class="form-label" for="payment-method-code-input">Codice mezzo di pagamento</label>
                            <input class="form-control" name="payment_method_code" id="payment-method-code-input" required/>
                        </div>
                    </div>
                    <div class="text-center mt-3">
                        <button class="btn btn-primary" type="submit" <?php if ($addresses_res->num_rows == 0) echo "disabled"; ?>>Effettua ordine</button>
                    </div>
                </form>
            </div>
        </main>
    </body>
</html>
