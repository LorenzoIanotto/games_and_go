<?php
require_once "../../../../../lib/auth.php";
require_once "../../../../../lib/db_actions/user.php";
require_once "../../../../../lib/db_actions/employee.php";
require_once "../../../../../lib/db_actions/address.php";
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
    $code = $_POST["code"];
    $role = $_POST["role"];

    if (!(
        isset($email) &&
        isset($password) &&
        isset($name) &&
        isset($surname) &&
        $birth_date &&
        isset($gender) &&
        $phone_number &&
        isset($code) &&
        isset($role)
    )) {
        die("Richiesta malformata");
    }

    $country_code = strlen($_POST["country_code"] ?? "") == 3 ? $_POST["country_code"] : null;
    $postcode = $_POST["postcode"];
    $city = $_POST["city"];
    $street = $_POST["street"];
    $house_number = $_POST["house_number"];
    $extension = intval($_POST["extension"]);

    if (!(isset($house_number) && isset($street) && isset($city) && isset($postcode) && isset($country_code))) {
        die("Richiesta malformata");
    }

    $address_id = get_address_id($house_number, $street, $city, $postcode, $country_code, $extension);
    
    if ($address_id === false) {
        die("Errore server");
    }

    if (!$address_id) {
        $address_id = insert_address($house_number, $street, $city, $postcode, $country_code, $extension);

        if ($address_id === false) {
            die("Errore server");
        }
    }

    $employee_id = insert_employee($email, $password, $name, $surname, $birth_date, $gender, $phone_number, $code, $role, $address_id);
    
    if (!is_numeric($employee_id)) {
        $error = $employee_id;
    } else {
        header("Location: /site/admin/users/");
    }
}
?>
<html>
    <head>
        <?php require "../../../../../components/bootstrap.php" ?>
        <title>Aggiunta Dipendente</title>
    </head>
    <body>
        <?php require "../../../../../components/headers/admin.php" ?>
        <main class="container">
            <!-- <div class="alert alert-danger alert-dismissible fade show php echo $show_err " role="alert"> -->
            <!--     <span><strong>Oopsie!</strong> Operazione fallita: controlla che l'utente non abbia dati a suo nome nel sistema. </span> -->
            <!--     <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button> -->
            <!-- </div> -->
            <div class="card">
                <form class="card-body" method="post">
                    <h1 class="card-title">Nuovo dipendente</h1>
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
                    <div class="row mb-4">
                        <div class="col">
                            <label class="form-label" for="code-input">Codice</label>
                            <input class="form-control <?php if ($error === InsertEmployeeError::CodeAlreadyInUse) echo "is-invalid" ?>" name="code" id="code-input" required/>
                            <span class="invalid-feedback">Codice già in uso</span>
                        </div>
                        <div class="col">
                            <label class="form-label" for="role-input">Ruolo</label>
                            <input class="form-control" name="role" id="role-input" required/>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col">
                            <label class="form-label" for="country-code-input">Codice Paese</label>
                            <input class="form-control" name="country_code" id="country-code-input" placeholder="AAA" pattern="[A-Z]{3}" required/>
                        </div>
                        <div class="col">
                            <label class="form-label" for="postcode-input">Codice postale</label>
                            <input class="form-control" name="postcode" id="postcode-input" required/>
                        </div>
                        <div class="col">
                            <label class="form-label" for="city-input">Città</label>
                            <input class="form-control" name="city" id="city-input" required/>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col">
                            <label class="form-label" for="street-input">Via/Piazza</label>
                            <input class="form-control" name="street" id="street-input" required/>
                        </div>
                        <div class="col">
                            <label class="form-label" for="house-number-input">Numero civico</label>
                            <input class="form-control" name="house_number" id="house-number-input" required/>
                        </div>
                        <div class="col">
                            <label class="form-label" for="extension-input">Interno</label>
                            <input class="form-control" name="extension" id="extension-input"/>
                        </div>
                    </div>
                    <button class="btn btn-primary" type="submit">Aggiungi</button>
                </form>
            </div>
        </main>
    </body>
</html>
