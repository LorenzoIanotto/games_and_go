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

$invalid_quantity_product_id = $_GET["invalid_quantity_product_id"] ?? null;


function sql_result_table(mysqli_result $res, array $products_with_quantity, int|null $invalid_quantity_product_id) {
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
        $id = intval($row["id"]);
        echo '<td class="'.($invalid_quantity_product_id === $id ? "table-danger" : "").'">'.$products_with_quantity[$id]."</td>";
        echo '<td><a class="btn btn-primary" href="/site/customer/select_product_quantity/?product_id='.$row["id"].'">Modifica</a><td>';
        echo "</tr>";
    }

    echo "</tbody></table>";
}

$show_err = $invalid_quantity_product_id ? "" : "d-none";
$err_msg = $invalid_quantity_product_id ? "<strong>Quantià eccessiva!</strong> Placa la tua sete di consumo." : null;
?>
<html>
    <head>
        <?php require "../../../components/bootstrap.php"; ?>
        <title>Carrello</title>
    </head>
    <body>
        <main class="container text-center">
            <div class="alert alert-danger alert-dismissible fade show <?php echo $show_err ?>" role="alert">
                <span><?php echo $err_msg ?></span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php
            if (sizeof($products_with_quantity) == 0) {
                echo "<span>Carrello vuoto</span>";
            } else {
                sql_result_table($res, $products_with_quantity, $invalid_quantity_product_id);
                echo '<a class="btn btn-primary" href="/site/customer/orders/new/">Procedi</a>';
            }
            ?>
        </main>
    </body>
</html>
