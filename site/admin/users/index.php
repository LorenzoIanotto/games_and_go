<?php
require_once "../../../lib/db.php";
require_once "../../../lib/auth.php";

session_start();
protect_page(UserRole::Admin);

function sql_result_table(mysqli_result $res) {
    echo "<table class=\"table\"><thead><tr>";

    while ($field = $res->fetch_field()) {
        echo "<th>".$field->name."</th>";
    }

    echo "<th></th></tr></thead><tbody>";

    while ($row = $res->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $field) {
            echo "<td>".$field."</td>";
        }
        echo '<td><form method="POST" action="delete.php"><input type="hidden" value="'.$row["id"].'" name="user_id"/><button class="btn btn-danger" type="submit">Elimina</input></form></td>';
        echo "</tr>";
    }

    echo "</tbody></table>";
    
}

$employee_code = $_GET["employee_code"] ?? null;

$conn = DatabaseConnection::get_instance();
$user_attributes = "id, email AS Email, name AS Nome, surname AS Cognome";

$customers = $conn->query("SELECT $user_attributes FROM User JOIN Customer ON User.id=Customer.user_id");
if ($employee_code) {
    $stmt = $conn->prepare("SELECT $user_attributes, code AS Codice, role AS Ruolo FROM User JOIN Employee ON User.id=Employee.user_id WHERE code=?");
    $stmt->bind_param("s", $employee_code);
    $stmt->execute();
    $employees = $stmt->get_result();
} else {
    $employees = $conn->query("SELECT $user_attributes, code AS Codice, role AS Ruolo FROM User JOIN Employee ON User.id=Employee.user_id");
}

$admins = $conn->query("SELECT $user_attributes FROM User JOIN Admin ON User.id=Admin.user_id");

$show_err = isset($_GET["delete_error"]) ? "" : "d-none";
?>
<html>
    <head>
        <?php require "../../../components/bootstrap.php" ?>
        <title>Gestione Utenti</title>
    </head>
    <body>
        <?php require "../../../components/headers/admin.php" ?>
        <main class="container">
            <div class="alert alert-danger alert-dismissible fade show <?php echo $show_err ?>" role="alert">
                <span><strong>Oopsie!</strong> Operazione fallita: controlla che l'utente non abbia dati a suo nome nel sistema. </span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <h2>Clienti</h2>
            <?php sql_result_table($customers) ?>
            <h2>Dipendenti</h2>
            <form>
                <label for="employee-code-input">Codice</label>
                <input name="employee_code" id="employee-code-input" value="<?php echo $employee_code ?? "" ?>"/>
                <button type="submit">Cerca</button>
            </form>
            <?php sql_result_table($employees) ?>
            <h2>Amministratori</h2>
            <?php sql_result_table($admins) ?>
        </main>
    </body>
</html>
