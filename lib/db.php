<?php

class DatabaseConnection extends mysqli {
    public function __construct() {
        parent::__construct("localhost", "root", "", "games_and_go");
    }
}
