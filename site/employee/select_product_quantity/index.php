<?php
require_once "../../../lib/auth.php";
require_once "../../../lib/db_actions/product.php";
session_start();
protect_page(UserRole::Employee);

$product_id = $_REQUEST["product_id"] ?? null;

if (!$product_id) {
    die("Richiesta malformata");
}

$products_with_quantity = $_SESSION["products_with_quantity"] ?? [];
$old_quantity = $products_with_quantity[$product_id] ?? 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST["quantity"])) {
        header("Location: /site/employee/");
        die();
    }

    if ($_POST["quantity"] > 0) {
        $products_with_quantity[$product_id] = $_POST["quantity"];
    } else {
        unset($products_with_quantity[$product_id]);
    }
    $_SESSION["products_with_quantity"] = $products_with_quantity;
    header("Location: /site/employee/cart/");
    die();
}

?>
<!DOCTYPE html>
<html>
    <head>
        <?php require "../../../components/bootstrap.php" ?>
        <title>Aggiungi all'ordine</title>
    </head>
    <body>
        <?php require "../../../components/headers/employee.php" ?>
        <main class="container">
            <form method="POST">
                <input type="hidden" value="<?php echo $product_id ?>" name="product_id"/>
                <label for="quantity-input">Quantità:</label>
                <input type="number" value="<?php echo $old_quantity ?>"name="quantity" id="quantity-input"/>
                <button type="submit">Inserisci</button>
            </form>
        </main>
    </body>
</html>
