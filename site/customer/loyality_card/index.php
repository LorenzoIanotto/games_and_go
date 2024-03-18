<?php
require_once "../../../lib/auth.php";
require_once "../../../lib/db_actions/customer.php";
session_start();
protect_page(UserRole::Customer);
$customer_id = get_user_id();
$loyality_card = get_loyality_card($customer_id);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($loyality_card) {
        die("Carta già esistente");
    }

    $loyality_card = insert_loyality_card($customer_id);
    if (!$loyality_card) {
        die("Errore");
    }
}
?>
<!DOCTYPE html>
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
