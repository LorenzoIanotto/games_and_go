<?php
require_once "../../../../lib/auth.php";
require_once "../../../../lib/db.php";
require_once "../../../../lib/db_actions/order.php";
session_start();
protect_page(UserRole::Customer);

$customer_id = get_user_id();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $payment_method = PaymentMethod::tryFrom($_POST["payment_method"]);
    $payment_method_code = $_POST["payment_method_code"];
    $address_id = intval($_POST["address_id"]);

    if (!($payment_method && $payment_method_code && $address_id)) {
        die("Richiesta malformata");
    }

    $products_with_quantity = $_SESSION["products_with_quantity"];
    $err = insert_customer_order($customer_id, $payment_method, $payment_method_code, $address_id, $products_with_quantity);
    
    if ($err == null) {
        $_SESSION["products_with_quantity"] = [];
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
        if ($key != "id") $str = $str.$value;
    }

    return $str;
}
?>
<html>
    <head>
        <?php require "../../../../components/bootstrap.php" ?>
        <title>Effettua ordine</title>
    </head>
    <body>
        <?php require "../../../../components/headers/customer.php" ?>
        <main class="container">
            <form method="POST">
                <?php
                if ($addresses_res->num_rows > 0) {
                    echo '<label for="address-id-input">Indirizzo</label>';
                    echo '<select name="address_id" id="address-id-input" required>';
                        while ($row = $addresses_res->fetch_assoc()) {
                            echo '"<option value="'.$row["id"].'">'.address_to_string($row)."</option>";
                        }
                    echo '</select>';
                }
                ?>
                <a href="/site/customer/add_address/">Devi aggiungere un indirizzo?</a>
                <label for="payment-method-input">Tipo di pagamento</label>
                <select name="payment_method" id="payment-method-input" required>
                    <option value="bancomat">Bancomat</option>
                    <option value="credit_card">Carta di credito</option>
                    <option value="cash_on_delivery">Contrassegno</option>
                    <option value="bank_transfer">Bonifico bancario</option>
                </select>
                <label for="payment-method-code-input">Codice mezzo di pagamento</label>
                <input name="payment_method_code" id="payment-method-code-input" required/>
                <button type="submit">Effettua ordine</button>
            </form>
        </main>
    </body>
</html>
