<?php

require_once "../../../lib/auth.php";
require_once "../../../lib/db.php";

session_start();
protect_page(UserRole::Customer);

$conn = DatabaseConnection::get_instance();

$products_with_quantity = $_SESSION["products_with_quantity"] ?? [];

$res = null;

if (sizeof($products_with_quantity) > 0) {
    $ids = array_keys($products_with_quantity);
    $placeholders = implode(',', array_fill(0, count($ids), '?')); // Es. ?,?,?, ...
    $param_types = str_repeat('i', count($ids)); // Es. iii

    $stmt = $conn->prepare("SELECT id, code AS Codice, price AS Prezzo FROM Product WHERE id IN ($placeholders)");
    $stmt->bind_param($param_types, ...$ids);
    $stmt->execute();
    $res = $stmt->get_result();
}


function sql_result_table(mysqli_result $res, array $products_with_quantity) {
    echo "<table class=\"table\"><thead><tr>";

    while ($field = $res->fetch_field()) {
        if ($field->name != "id") {
            echo "<th>".$field->name."</th>";
        }
    }
    echo "<th>Quantità selezionata</th><th></th>";

    echo "</tr></thead><tbody>";

    while ($row = $res->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $key => $field) {
            if ($key != "id") {
                echo "<td>".$field."</td>";
            }
        }
        echo "<td>".$products_with_quantity[$row["id"]]."</td>";
        echo '<td><a class="btn btn-primary" href="/site/customer/select_product_quantity/?product_id='.$row["id"].'">Modifica</a><td>';
        echo "</tr>";
    }

    echo "</tbody></table>";
}

?>
<html>
    <head>
        <?php require "../../../components/bootstrap.php"; ?>
        <title>Carrello</title>
    </head>
    <body>
        <main class="container">
            <?php
            if (sizeof($products_with_quantity) == 0) {
                echo "<span>Carrello vuoto</span>";
            } else {
                sql_result_table($res, $products_with_quantity);
            }
            ?>
        </main>
    </body>
</html>
