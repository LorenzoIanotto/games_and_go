<?php
require_once "../../lib/auth.php";
require_once "../../lib/db.php";
session_start();
protect_page(UserRole::Admin);

$date_from = DateTime::createFromFormat("Y-m-d", $_GET["date_from"] ?? "") ? $_GET["date_from"] : "1970-01-01";
$date_to = DateTime::createFromFormat("Y-m-d", $_GET["date_to"] ?? "") ? $_GET["date_to"] : "9999-12-31";

$conn = DatabaseConnection::get_instance();
$stmt = $conn->prepare("SELECT created_at AS Data, name AS Nome, surname AS Cognome, total_amount AS Totale, payment_method AS `Metodo di pagamento` FROM User
    JOIN CustomerOrder ON User.id=CustomerOrder.customer_id
    WHERE created_at BETWEEN ? AND ?
    ORDER BY created_at ASC");
$stmt->bind_param("ss", $date_from, $date_to);
$stmt->execute();
$res = $stmt->get_result();


function sql_result_table(mysqli_result $res) {
    echo "<table class=\"table\"><thead><tr>";

    while ($field = $res->fetch_field()) {
        echo "<th>".$field->name."</th>";
    }

    echo "</tr></thead><tbody>";

    while ($row = $res->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $field) {
            echo "<td>".$field."</td>";
        }
        echo "</tr>";
    }

    echo "</tbody></table>";
}
?>
<html>
    <head>
        <?php require("../../components/bootstrap.php"); ?>
        <title>Homepage Admin</title>
    </head>
    <body>
        <?php require "../../components/headers/admin.php" ?>
        <main class="container">
            <form>
                <h1 class="mb-3">Visualizza Ordini</h1>
                <label for="date-from-input">Da</label>
                <input type="date" name="date_from" id="date-from-input" value="<?php echo ($_GET["date_from"] ?? false) ? $_GET["date_from"] : "" ?>"/>
                <label for="date-to-input">A</label>
                <input type="date" name="date_to" id="date-to-input" value="<?php echo ($_GET["date_to"] ?? false) ? $_GET["date_to"] : "" ?>"/>
                <button class="btn btn-primary" type="submit">Cerca</button>
            </form>
            <?php sql_result_table($res) ?>
        </main>
    </body>
</html>
