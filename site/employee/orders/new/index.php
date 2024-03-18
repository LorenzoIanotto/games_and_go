<?php
require_once "../../../../lib/auth.php";
require_once "../../../../lib/db.php";
require_once "../../../../lib/db_actions/employee_order.php";
require_once "../../../../lib/db_actions/employee.php";
session_start();
protect_page(UserRole::Employee);

$employee_id = get_user_id();
$products_with_quantity = $_SESSION["products_with_quantity"] ?? [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $vendor_id = $_POST["vendor_id"] ?? null;
    $total_amount = $_POST["total_amount"] ?? null;

    if (!$vendor_id || !$total_amount || doubleval($total_amount) < 0) die("Richiesta malformata");

    $res = insert_employee_order($employee_id, $vendor_id, $total_amount, $products_with_quantity);
    
    if ($res) {
        $_SESSION["products_with_quantity"] = [];
    } else {
        die("Errore");
    }

    header("Location: /site/employee/");
    die();
}

$conn = DatabaseConnection::get_instance();
$vendor_res = $conn->query("SELECT * FROM Vendor");

if (!$vendor_res) die("Errore");

?>
<html>
    <head>
        <?php require "../../../../components/bootstrap.php" ?>
        <title>Effettua ordine</title>
    </head>
    <body>
        <?php require "../../../../components/headers/employee.php" ?>
        <main class="container">
            <div class="card">
                <form method="POST" class="card-body">
                    <div class="row row-cols-2 g-3">
                        <div class="col">
                            <?php
                            if ($vendor_res->num_rows > 0) {
                                echo '<label class="form-label" for="vendor-id-input">Fornitore</label>';
                                echo '<select class="form-control form-select" name="vendor_id" id="vendor-id-input" required>';
                                while ($row = $vendor_res->fetch_assoc()) {
                                    echo '"<option value="'.$row["id"].'">'.$row["vat_number"]." ".$row["business_name"]."</option>";
                                }
                                echo '</select>';
                            }
                            ?>
                            <a class="form-text" href="/site/employee/add_vendor/">Devi aggiungere un fornitore?</a>
                        </div>
                        <div class="col">
                            <label class="form-label" for="total-amount-input">Totale</label>
                            <input class="form-control" min="0" step="0.01" type="number" name="total_amount" id="total-amount-input"/>
                        </div>
                    </div>
                    <div class="text-center mt-3">
                        <button class="btn btn-primary" type="submit" <?php if ($vendor_res->num_rows == 0) echo "disabled"; ?>>Effettua ordine</button>
                    </div>
                </form>
            </div>
        </main>
    </body>
</html>
