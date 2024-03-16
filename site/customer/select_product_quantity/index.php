<?php
require_once "../../../lib/auth.php";
session_start();
protect_page(UserRole::Customer);

$product_id = $_REQUEST["product_id"];

if (!$product_id) {
    die("Richiesta malformata");
}

$products_with_quantity = $_SESSION["products_with_quantity"] ?? [];
$quantity = $products_with_quantity[$product_id] ?? 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST["quantity"])) {
        header("Location: /site/customer/");
        die();
    }

    // TODO: Controlla che la quantità non sia eccessiva

    $product_id = $_POST["product_id"];

    $products_with_quantity[$product_id] = $_POST["quantity"];
    $_SESSION["products_with_quantity"] = $products_with_quantity;
    header("Location: /site/customer/cart/");
    die();
}

?>
<html>
    <head>
        <?php require "../../../components/bootstrap.php" ?>
        <title>Aggiungi al carrello</title>
    </head>
    <body>
        <main class="container">
            <form method="POST">
                <input type="hidden" value="<?php echo $product_id ?>" name="product_id"/>
                <label for="quantity-input">Quantità:</label>
                <input type="number" value="<?php echo $quantity ?>"name="quantity" id="quantity-input"/>
                <button type="submit">Inserisci</button>
            </form>
        </main>
    </body>
</html>
