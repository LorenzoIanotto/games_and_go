<?php
require_once "../../lib/db.php";

class PhoneNumber {
    private int $value;

    private function __construct(int $num) {
        $this->value = $num;
    }

    public static function from_string(string $str): PhoneNumber|null {
        $num = intval($str);

        if (!$num) return null;

        return self::from_number($num);
    }

    public static function from_number(int $num): PhoneNumber|null {
        if ($num > 0 && $num < 10000000000) {
            return new PhoneNumber($num);
        }

        return null;
    }

    public function get_value(): int {
        return $this->value;
    }
}

enum InsertUserError {
    case AlreadyExistentUser;
    case DatabaseError;
}

function insert_user(string $email, string $password, string $name, string $surname, DateTime $birth_date, string $gender, PhoneNumber $phone_number, DatabaseConnection $conn = null): InsertUserError|int {

    $password_hash = hash("sha256", $password);

    $conn = DatabaseConnection::get_instance();
    $stmt = $conn->prepare("SELECT COUNT(email) AS count FROM User WHERE email = (?)");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    if (!$res) {
        return InsertUserError::DatabaseError;
    }

    if ($res->fetch_assoc()["count"] > 0) {
        return InsertUserError::AlreadyExistentUser;
    }

    $stmt = $conn->prepare("INSERT INTO User (email, password_hash, name, surname, birth_date, gender, phone_number)
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssi", $email, $password_hash, $name, $surname, $birth_date->format("Y-m-d"), $gender, $phone_number->get_value());
    $stmt->execute();
    $id = mysqli_insert_id($conn);
    
    if ($id) {
        return $id;
    }

    return InsertUserError::DatabaseError;
}
