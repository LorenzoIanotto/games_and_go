<?php
require_once "../../../lib/auth.php";
session_start();
protect_page(UserRole::Employee);

$conn = DatabaseConnection::get_instance();
$games_res = $conn->query("SELECT id, name AS Nome, code AS Codice, price AS Prezzo, quantity AS Quantità, plot AS Trama FROM Game JOIN Product ON Game.product_id = Product.id");
$consoles_res = $conn->query("SELECT id, name AS Nome, code AS Codice, price AS Prezzo, quantity AS Quantità, type AS Tipo FROM Console JOIN Product ON Console.product_id = Product.id");
$accessory_res = $conn->query("SELECT id, name AS Nome, code AS Codice, price AS Prezzo, quantity AS Quantità, type AS Tipo FROM Accessory JOIN Product ON Accessory.product_id = Product.id");
$games_guides_res = $conn->query("SELECT id, name AS Nome, code AS Codice, price AS Prezzo, quantity AS Quantità FROM GameGuide JOIN Product ON GameGuide.product_id = Product.id");

function sql_result_table(mysqli_result $res) {
    echo "<table class=\"table\"><thead><tr>";

    while ($field = $res->fetch_field()) {
        if ($field->name != "id") {
            echo "<th>".$field->name."</th>";
        }
    }
    echo "<th></th>";

    echo "</tr></thead><tbody>";

    while ($row = $res->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $key => $field) {
            if ($key != "id") {
                echo "<td>".$field."</td>";
            }
        }
        echo '<td><a class="btn btn-primary" href="/site/employee/products/details/?product_id='.$row["id"].'">Dettagli</a><td>';
        echo '<td><a class="btn btn-primary" href="/site/employee/select_product_quantity/?product_id='.$row["id"].'">Aggiungi a ordine</a><td>';
        echo "</tr>";
    }

    echo "</tbody></table>";
}

?>
<html>
    <head>
        <?php require "../../../components/bootstrap.php" ?>
        <title>Visualizza prodotti</title>
    </head>
    <body>
        <?php require "../../../components/headers/employee.php" ?>
        <main class="container">
            <h2>Giochi</h2>
            <?php sql_result_table($games_res) ?>
            <h2>Console</h2>
            <?php sql_result_table($consoles_res) ?>
            <h2>Accessori</h2>
            <?php sql_result_table($accessory_res) ?>
            <h2>Guide ai Giochi</h2>
            <?php sql_result_table($games_guides_res) ?>
        </main>
    </body>
</html>
