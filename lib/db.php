<?php

class DatabaseConnection extends mysqli {
    public function __construct() {
        parent::__construct("localhost", "root", "", "games_and_go");
    }
}

enum RegistrationError {
    case AlreadyExistantUser;
    case WrongFormat;
    case DatabaseError;
}

function insert_user(string $email, string $password, string $name, string $surname, string $birth_date, string $gender, int $phone_number): RegistrationError|int {
    require_once "../../lib/db.php";

    function is_phone_number(int $phone_number): bool {
        if ($phone_number < 0) {
            return false;
        }

        if (strlen($phone_number) > 10) {
            return false;
        }

        return $phone_number;
    }
    
    if (!is_phone_number($phone_number)) {
        return RegistrationError::WrongFormat;
    }

    $password_hash = hash("sha256", $password);

    $conn = new DatabaseConnection();
    $stmt = $conn->prepare("SELECT COUNT(email) AS count FROM Users WHERE email = (?)");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    if (!$res) {
        $conn->close();
        return RegistrationError::DatabaseError;
    }

    if ($res->fetch_assoc()["count"] > 0) {
        $conn->close();
        return RegistrationError::AlreadyExistantUser;
    }

    $stmt = $conn->prepare("INSERT INTO Users (email, password_hash, name, surname, birth_date, gender, phone_number)
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssi", $email, $password_hash, $name, $surname, $birth_date, $gender, $phone_number);
    $stmt->execute();
    $id = mysqli_insert_id($conn);
    $conn->close();
    
    if ($id) {
        return $id;
    }

    return RegistrationError::DatabaseError;
}
