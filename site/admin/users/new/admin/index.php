<?php
require_once "../../../../../lib/auth.php";
require_once "../../../../../lib/db_actions/admin.php";
session_start();
protect_page(UserRole::Admin);

$error = null;

if ($_SERVER["REQUEST_METHOD"] == "POST"){
    $birth_date = DateTime::createFromFormat("Y-m-d", $_POST["birth_date"] ?? "");
    $phone_number = PhoneNumber::from_string($_POST["phone_number"] ?? "");
    $email = $_POST["email"];
    $password = $_POST["password"];
    $name = $_POST["name"];
    $surname = $_POST["surname"];
    $gender = $_POST["gender"];

    if (!(
        isset($email) &&
        isset($password) &&
        isset($name) &&
        isset($surname) &&
        $birth_date &&
        isset($gender) &&
        $phone_number
    )) {
        die("Richiesta malformata");
    }

    $admin_id = insert_admin($email, $password, $name, $surname, $birth_date, $gender, $phone_number);
    
    if (!is_numeric($admin_id)) {
        $error = $admin_id;
    } else {
        header("Location: /site/admin/users/");
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <?php require "../../../../../components/bootstrap.php" ?>
        <title>Aggiunta Amministratore</title>
    </head>
    <body>
        <?php require "../../../../../components/headers/admin.php" ?>
        <main class="container">
            <div class="card">
                <form class="card-body" method="post">
                    <h1 class="card-title">Nuovo amministratore</h1>
                    <div class="row mb-2">
                        <div class="col">
                            <label class="form-label" for="email-input">Email</label>
                            <input class="form-control <?php if ($error === InsertUserError::AlreadyExistentUser) echo "is-invalid" ?>" type="email" name="email" id="email-input" required/>
                            <span class="invalid-feedback">Utente già esistente</span>
                        </div>
                        <div class="col">
                            <label class="form-label" for="password-input">Password</label>
                            <input class="form-control" type="password" name="password" id="password-input" required/>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col">
                            <label class="form-label" for="name-input">Nome</label>
                            <input class="form-control" type="text" name="name" id="name-input" required/>
                        </div>
                        <div class="col">
                            <label class="form-label" for="surname-input">Cognome</label>
                            <input class="form-control" type="text" name="surname" id="surname-input" required/>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col">
                            <label class="form-label" for="birth-date-input">Data di nascita</label>
                            <input class="form-control" type="date" name="birth_date" id="birth-date-input" required/>
                        </div>
                        <div class="col">
                            <label class="form-label" for="gender-input">Sesso</label>
                            <select class="form-control form-select" name="gender" id="gender-input" required>
                                <option hidden disabled selected value></option>
                                <option value="male">Uomo</option>
                                <option value="female">Donna</option>
                                <option value="other">Australopiteco</option>
                            </select>
                        </div>
                        <div class="col">
                            <label class="form-label" for="phone-number-input">Numero di telefono</label>
                            <input class="form-control" type="tel" name="phone_number" id="phone-number-input" pattern="[0-9]{3}[0-9]{3}[0-9]{4}" required/>
                        </div>
                    </div>
                    <button class="btn btn-primary" type="submit">Aggiungi</button>
                </form>
            </div>
        </main>
    </body>
</html>
