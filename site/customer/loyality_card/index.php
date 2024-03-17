<?php
require_once "../../../lib/auth.php";
require_once "../../../lib/db_actions/customer.php";
session_start();
protect_page(UserRole::Customer);
$customer_id = get_user_id();
$loyality_card = get_loyality_card($customer_id);

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $country_code = $_POST["country_code"];
    if (strlen($country_code) != 3) {
        $country_code = null;
    }
    $postcode = $_POST["postcode"];
    $city = $_POST["city"];
    $street = $_POST["street"];
    $house_number = $_POST["house_number"];
    $extension = intval($_POST["extension"]);

    if (!(isset($house_number) && isset($street) && isset($city) && isset($postcode) && isset($country_code))) {
        die("Richiesta malformata");
    }

    $res = add_address_to_customer($customer_id, $house_number, $street, $city, $postcode, $country_code, $extension);

    if ($res !== null) {
        header("Location: /site/customer/orders/new/");
    } else {
        die("Si è verificato un errore");
    }
}
?>
<html>
    <head>
        <?php require "../../../components/bootstrap.php" ?>
        <title>Gestione tessera</title>
    </head>
    <body>
        <?php require "../../../components/headers/customer.php" ?>
        <main class="container">
            <?php
            if ($loyality_card) {
                echo
                    "<div>Numero carta: <strong>$loyality_card->number</strong></div>".
                    "<div>Punti: <strong>$loyality_card->points</strong></div>";
            } else {
                echo '<form method="POST"><button type="submit" class="btn btn-primary">Aggiungi tessera</button></form>';
            }
            ?>
        </main>
    </body>
</html>
