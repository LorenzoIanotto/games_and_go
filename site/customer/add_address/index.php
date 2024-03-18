<?php
require_once "../../../lib/auth.php";
require_once "../../../lib/db_actions/address.php";
session_start();
protect_page(UserRole::Customer);
$customer_id = get_user_id();

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
<!DOCTYPE html>
<html>
    <head>
        <?php require "../../../components/bootstrap.php" ?>
        <title>Aggiungi indirizzo</title>
    </head>
    <body>
        <?php require "../../../components/headers/customer.php" ?>
        <main class="container">
            <form method="POST">
                <label for="country-code-input">Codice Paese</label>
                <input name="country_code" id="country-code-input" placeholder="AAA" pattern="[A-Z]{3}" required/>
                <label for="postcode-input">Codice postale</label>
                <input name="postcode" id="postcode-input" required/>
                <label for="city-input">Città</label>
                <input name="city" id="city-input" required/>
                <label for="street-input">Via/Piazza</label>
                <input name="street" id="street-input" required/>
                <label for="house-number-input">Numero civico</label>
                <input name="house_number" id="house-number-input" required/>
                <label for="extension-input">Interno</label>
                <input name="extension" id="extension-input"/>
                <button type="submit">Aggiungi</button>
            </form>
        </main>
    </body>
</html>
