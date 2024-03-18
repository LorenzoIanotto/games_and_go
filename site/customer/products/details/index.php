<?php
require_once "../../../../lib/auth.php";
require_once "../../../../lib/db.php";
require_once "../../../../lib/db_actions/customer.php";
session_start();
protect_page(UserRole::Customer);

$product_id = $_GET["product_id"] ?? null;

if (!$product_id) die("Richiesta malformata");

$conn = DatabaseConnection::get_instance();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $summary = $_POST["summary"] ?? null;
    $description = $_POST["description"] ?? null;
    $rating = intval($_POST["rating"] ?? "");

    if (!(
        $summary &&
        $description &&
        $rating &&
        $rating >= 0 &&
        $rating <= 5
    )) {
        die("Richiesta malformata");
    }

    $res = insert_user_feedback($summary, $description, $rating, get_user_id(), $product_id);

    if (!$res) die("Errore");
}

$products = array();
$products[] = $conn->prepare("
    SELECT name AS Nome, code AS Codice, price AS Prezzo, quantity AS Quantità, plot AS Trama
    FROM Game JOIN Product ON Game.product_id = Product.id
    WHERE product_id=?
");
$products[] = $conn->prepare("
    SELECT name AS Nome, code AS Codice, price AS Prezzo, quantity AS Quantità, type AS Tipo
    FROM Console JOIN Product ON Console.product_id = Product.id
    WHERE product_id=?
");
$products[] = $conn->prepare("
    SELECT name AS Nome, code AS Codice, price AS Prezzo, quantity AS Quantità, type AS Tipo
    FROM Accessory JOIN Product ON Accessory.product_id = Product.id
    WHERE product_id=?
");
$products[] = $conn->prepare("
    SELECT name AS Nome, code AS Codice, price AS Prezzo, quantity AS Quantità
    FROM GameGuide JOIN Product ON GameGuide.product_id = Product.id
    WHERE product_id=?
");

$product = null;

foreach ($products as $stmt) {
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();

    if ($res) {
        $product = $res;
        break;
    }
}

$stmt = $conn->prepare("
    SELECT customer_id, name, surname, summary, description, rating
    FROM UserFeedback
    JOIN User ON User.id=UserFeedback.customer_id
    WHERE product_id=?
");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$ratings = $stmt->get_result();

function print_review(array $rating, int $product_id) {
    $delete_form = '
        <form method="POST" action="./feedback/delete.php" class="mt-3">
            <input type="hidden" name="product_id" value="'.$product_id.'"/>
            <button type="submit" class="btn btn-danger">Elimina</button>
        </form>
    ';

    echo
        '<div class="card">'.
            '<h6 class="card-header">'.$rating["name"]." ".$rating["surname"]."</h6>".
            '<div class="card-body">'.
                '<h4 class="card-title">'.$rating["summary"]."</h4>".
                '<div class="card-text">'.$rating["description"]."</div>".
                "<strong>".$rating["rating"]."</strong>/5".
                ($rating["customer_id"] == get_user_id() ? $delete_form : "").
            "</div>".
        "</div>";

}

$stmt = $conn->prepare("SELECT AVG(rating) as average_rating FROM UserFeedback WHERE product_id=?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$average_rating = $stmt->get_result()->fetch_assoc()["average_rating"];
$average_rating = number_format($average_rating, 1, ",");

$customer_has_already_reviewed = false;
?>
<!DOCTYPE html>
<html>
    <head>
        <?php require "../../../../components/bootstrap.php" ?>
        <title>Dettagli prodotto</title>
    </head>
    <body>
        <?php require "../../../../components/headers/customer.php" ?>
        <main class="container">
            <section>
                <h2>Dettagli</h2>
                <div>
                    <?php
                    foreach ($product as $key => $value) {
                        echo
                            "<div>".
                                "<strong>$key: </strong>".
                                "<span>$value</span>".
                            "</div>";
                    }
                    ?>
                </div>
            </section>
            <section class="mt-3 row row-cols-1">
                <h2>Recensioni</h2>
                <div>Media: <strong><?php echo $average_rating ?></strong>/5</div>
                <div class="mt-0 row row-cols-1 g-3">
                    <?php
                    if ($ratings->num_rows === 0) echo "Nessuna recensione";

                    while ($row = $ratings->fetch_assoc()) {
                        if ($row["customer_id"] == get_user_id()) $customer_has_already_reviewed = true;

                        print_review($row, $product_id);
                    }
                    ?>
                    <form class="card" method="POST" action="./feedback/create.php">
                        <h6 class="card-header">Nuova recensione</h6>
                        <div class="card-body">
                            <input type="hidden" name="product_id" value="<?php echo $product_id ?>"/>
                            <input type="text" name="summary" class="form-control card-title" placeholder="Titolo..." required/>
                            <textarea name="description" class="mb-3 card-text form-control" placeholder="Descrizione..."></textarea>
                            <div class="mb-3 input-group">
                                <input name="rating" type="number" min="0" max="5" style="max-width: 7ch;" class="form-control" required/>
                                <span class="input-group-text">/5</span>
                            </div>
                            <button type="submit" class="btn btn-primary" <?php echo $customer_has_already_reviewed ? "disabled" : ""; ?>>Aggiungi</button>
                        </div>
                    </form>
                </div>
            </section>
        </main>
    </body>
</html>
