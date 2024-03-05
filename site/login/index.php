<?php
if (isset( $_POST["email"]) && isset($_POST["password"])) {
    session_start();
    // $_SESSION["user_id"] = 
}
?>
<html>
    <head>
        <title>Accedi</title>
    </head>
    <body>
        <form method="post">
            <label for="email-input">Email:</label>
            <input type="email" name="email" id="email-input" />
            <label for="password-input-input">Password:</label>
            <input type="password" name="password" id="password-input" />
            <button type="submit">Accedi</button>
        </form>
    </body>
</html>
