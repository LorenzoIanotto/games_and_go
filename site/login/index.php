<?php
require_once "../../lib/auth.php";

$error = null;

if (isset($_POST["email"]) && isset($_POST["password"])) {
    session_start();

    $res = login($_POST["email"], $_POST["password"]);

    if (is_int($res)) {
        $_SESSION["user_id"] = $res;
        header("Location: /site/");
        die();
    }

    $error = $res;
}
?>
<html>
    <head>
        <?php require("../../components/bootstrap.php"); ?>
        <title>Accedi</title>
    </head>
    <body class="container min-vh-100 justify-content-center align-items-center d-flex flex-column">
        <div class="card">
            <form class="card-body" method="post">
                <h1 class="card-title">Accedi</h1>
                <div class="mb-2">
                    <label class="form-label" for="email-input">Email</label>
                    <input class="form-control <?php if ($error === LoginError::NonExistantUser) echo "is-invalid" ?>" type="email" name="email" id="email-input" />
                    <span class="invalid-feedback">Utente non esistente</span>
                </div>
                <div class="mb-4">
                    <label class="form-label" for="password-input">Password</label>
                    <input class="form-control <?php if ($error === LoginError::WrongPassord) echo "is-invalid" ?> "type="password" name="password" id="password-input" />
                    <span class="invalid-feedback"">Password errata</span>
                </div>
                <button class="btn btn-primary" type="submit">Accedi</button>
                <span class="ms-2 align-middle">
                    Non sei ancora iscritto? <a href="/site/register">Registrati</a>
                </span>
            </form>
        </div>
    </body>
</html>
