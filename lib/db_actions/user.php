<?php

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

function insert_user(
    string $email,
    string $password,
    string $name,
    string $surname,
    DateTime $birth_date,
    string $gender,
    PhoneNumber $phone_number
): InsertUserError|int {

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
    $birth_date_str = $birth_date->format("Y-m-d");
    $phone_number_value = $phone_number->get_value();
    $stmt->bind_param("ssssssi", $email, $password_hash, $name, $surname, $birth_date_str, $gender, $phone_number_value);
    $stmt->execute();
    $id = mysqli_insert_id($conn);
    
    if ($id) {
        return $id;
    }

    return InsertUserError::DatabaseError;
}

function delete_user(int $user_id): bool {
    $conn = DatabaseConnection::get_instance();
    $conn->begin_transaction();

    try {
        foreach (UserRole::cases() as $role) {
            $stmt = $conn->prepare("DELETE IGNORE FROM $role->value WHERE user_id=?");
            $stmt->bind_param("i", $user_id);
            $res = $stmt->execute();

            if (!$res) {
                throw new Error();
            }
        }


        $stmt = $conn->prepare("DELETE FROM User WHERE id=?");
        $stmt->bind_param("i", $user_id);
        $res = $stmt->execute();

        if (!$res) {
            $conn->rollback();
            return false;
        }
    } catch (\Throwable) {
        $conn->rollback();
        return false;
    }

    $conn->commit();
    return true;
}
