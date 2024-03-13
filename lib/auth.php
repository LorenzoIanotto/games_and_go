<?php
require_once "db.php";

enum LoginError {
    case WrongPassord;
    case NonExistantUser;
}

function login(string $email, string $password): LoginError|int {
    $conn = new DatabaseConnection();
    $stmt = $conn->prepare("SELECT id, password_hash FROM Users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user) {
        return LoginError::NonExistantUser;
    }

    $password_hash = hash("sha256", $password);

    if ($password_hash !== $user["password_hash"]) {
        return LoginError::WrongPassord;
    }


    return intval($user["id"]);
}

