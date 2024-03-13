<?php
require "../../lib/db.php";

$error = null;

if ($_SERVER["REQUEST_METHOD"] == "POST"){
    if (!(isset($_POST["email"]) &&
        isset($_POST["password"]) &&
        isset($_POST["name"]) &&
        isset($_POST["surname"]) &&
        isset($_POST["birth_date"]) &&
        isset($_POST["gender"]) &&
        isset($_POST["phone_number"]) &&
        is_numeric($_POST["phone_number"])
    )) {
        die("Richiesta malformata");
    }

    $res = insert_user($_POST["email"],
        $_POST["password"],
        $_POST["name"],
        $_POST["surname"],
        $_POST["birth_date"],
        $_POST["gender"],
        intval($_POST["phone_number"]));

    if (is_int($res)) {
        header("Location: /site/login/");
    } else {
        $error = $res;
    }
}
?>
<html>
    <head>
        <?php require("../../components/bootstrap.php"); ?>
        <title>Registrati</title>
    </head>
    <body class="container min-vh-100 justify-content-center d-flex flex-column">
        <div class="card">
            <form class="card-body" method="post">
                <h1 class="card-title">Nuovo utente</h1>
                <div class="form-row">
                    <div class="form-group col">
                        <label for="email-input">Email</label>
                        <input class="form-control <?php if ($error === RegistrationError::AlreadyExistantUser) echo "is-invalid" ?>" type="email" name="email" id="email-input" required/>
                        <span class="invalid-feedback">Utente già esistente</span>
                    </div>
                    <div class="form-group col">
                        <label for="password-input">Password</label>
                        <input class="form-control" type="password" name="password" id="password-input" required/>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col">
                        <label for="name-input">Nome</label>
                        <input class="form-control" type="text" name="name" id="name-input" required/>
                    </div>
                    <div class="form-group col">
                        <label for="surname-input">Cognome</label>
                        <input class="form-control" type="text" name="surname" id="surname-input" required/>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col">
                        <label for="birth-date-input">Data di nascita</label>
                        <input class="form-control" type="date" name="birth_date" id="birth-date-input" required/>
                    </div>
                    <div class="form-group col">
                        <label for="gender-input">Sesso</label>
                        <select class="form-control" name="gender" id="gender-input" required>
                            <option hidden disabled selected value></option>
                            <option value="male">Uomo</option>
                            <option value="female">Donna</option>
                            <option value="other">Australopiteco</option>
                        </select>
                    </div>
                    <div class="form-group col">
                        <label for="phone-number-input">Numero di telefono</label>
                        <input class="form-control" type="tel" name="phone_number" id="phone-number-input" pattern="[0-9]{3}[0-9]{3}[0-9]{4}" required/>
                    </div>
                </div>
                <button class="btn btn-primary" type="submit">Registrati</button>
                <span class="ml-2">
                    Sei già iscritto? <a href="/site/login">Accedi</a>
                </span>
            </form>
        </div>
    </body>
</html>
