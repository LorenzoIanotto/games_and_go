<?php

class DatabaseConnection {
    private static ?mysqli $connection = null;

    public static function get_instance(): mysqli {
        if (self::$connection === null) {
            self::$connection = new mysqli("localhost", "root", "", "games_and_go");
        }

        return self::$connection;
    }
}
