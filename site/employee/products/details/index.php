<?php
require_once "../../../../lib/auth.php";
require_once "../../../../lib/db.php";
session_start();
protect_page(UserRole::Employee);

$product_id = $_GET["product_id"] ?? null;

if (!$product_id) die("Richiesta malformata");

$conn = DatabaseConnection::get_instance();

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

$stmt = $conn->prepare("SELECT name, surname, summary, description, rating FROM UserFeedback JOIN User ON User.id=UserFeedback.customer_id WHERE product_id=?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$ratings = $stmt->get_result();

function print_review(array $rating) {
    echo
        '<div class="card">'.
            '<h6 class="card-header">'.$rating["name"]." ".$rating["surname"]."</h6>".
            '<div class="card-body">'.
                '<h4 class="card-title">'.$rating["summary"]."</h4>".
                '<div class="card-text">'.$rating["description"]."</div>".
                "<strong>".$rating["rating"]."</strong>/5".
            "</div>".
        "</div>";

}

$stmt = $conn->prepare("SELECT AVG(rating) as average_rating FROM UserFeedback WHERE product_id=?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$average_rating = $stmt->get_result()->fetch_assoc()["average_rating"];
$average_rating = number_format($average_rating, 1, ",");

?>
<html>
    <head>
        <?php require "../../../../components/bootstrap.php" ?>
        <title>Dettagli prodotto</title>
    </head>
    <body>
        <?php require "../../../../components/headers/employee.php" ?>
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
                        print_review($row);
                    }
                    ?>
                </div>
            </section>
        </main>
    </body>
</html>
