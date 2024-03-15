<?php
require_once "db.php";

enum LoginError {
    case WrongPassord;
    case NonExistantUser;
}

function login(string $email, string $password): LoginError|int {
    $conn = DatabaseConnection::get_instance();
    $stmt = $conn->prepare("SELECT id, password_hash FROM User WHERE email = ?");
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

function get_user_id(): int|null {
    if (isset($_SESSION["user_id"])) {
        return intval($_SESSION["user_id"]);
    }

    return null;
}

enum UserRole: string {
    case Customer = "Customer";
    case Admin = "Admin";
    case Employee = "Employee";
}

function protect_page(UserRole $role): void {
    $id = get_user_id();

    function redirect() {
        header("Location: /site/login/");
        die();
    }

    if ($id === null) redirect();

    $conn = DatabaseConnection::get_instance();
    $stmt = $conn->prepare("SELECT * FROM `".$role->value."` WHERE user_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();

    if (!$res) redirect();

    $row = $res->fetch_assoc();

    if (!$row) redirect();
}
