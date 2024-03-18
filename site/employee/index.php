<?php
require_once "../../lib/auth.php";
session_start();
protect_page(UserRole::Employee);
?>
<!DOCTYPE html>
<html>
    <head>
        <?php require "../../components/bootstrap.php" ?>
        <title>Homepage Dipendente</title>
    </head>
    <body>
        <?php require "../../components/headers/employee.php" ?>
        <main class="container"></main>
    </body>
</html>
