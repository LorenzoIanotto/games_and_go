<?php
    require "../lib/auth.php";
    session_start();
?>
<html>
    <head>
        <title>Home</title>
    </head>
    <body>
        <?php echo get_user_id() ?>
    </body>
</html>
