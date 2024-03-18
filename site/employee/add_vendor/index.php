<?php
require_once "../../../lib/auth.php";
require_once "../../../lib/db_actions/address.php";
require_once "../../../lib/db_actions/vendor.php";
session_start();
protect_page(UserRole::Employee);
$employee_id = get_user_id();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $vat = VatNumber::try_from($_POST["vat"] ?? "");
    $business_name = $_POST["business_name"] ?? null;
    $email = $_POST["email"] ?? null;
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

    $address_id = get_address_id($house_number, $street, $city, $postcode, $country_code, $extension);

    if (!$address_id) {
        $address_id = insert_address($house_number, $street, $city, $postcode, $country_code, $extension);

        if (!$address_id) die("Errore");
    }

    $res = insert_vendor($vat, $business_name, $email, $address_id);

    if ($res !== false) {
        header("Location: /site/employee/orders/new/");
    } else {
        die("Si è verificato un errore");
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <?php require "../../../components/bootstrap.php" ?>
        <title>Aggiungi fornitore</title>
    </head>
    <body>
        <?php require "../../../components/headers/employee.php" ?>
        <main class="container">
            <form method="POST">
                <label for="vat-input">Partita IVA</label>
                <input name="vat" id="vat-input"/>
                <label for="business-name-input">Ragione sociale</label>
                <input name="business_name" id="business-name-input"/>
                <label for="email-input">email</label>
                <input name="email" id="email-input"/>
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
