<?php
require_once (__DIR__ . "/../db.php");
require_once (__DIR__ . "/user.php");

enum InsertEmployeeError {
    case CodeAlreadyInUse;
}

function insert_employee(
    string $email,
    string $password,
    string $name,
    string $surname,
    DateTime $birth_date,
    string $gender,
    PhoneNumber $phone_number,
    string $code,
    string $role,
    int $address_id
): int|InsertUserError|InsertEmployeeError {
    $conn = DatabaseConnection::get_instance();
    $conn->begin_transaction();

    $res = insert_user($email, $password, $name, $surname, $birth_date, $gender, $phone_number);

    if ($res instanceof InsertUserError) {
        $conn->rollback();
        return $res;
    }
    $user_id = $res;

    $stmt = $conn->prepare("SELECT COUNT(code) as count FROM Employee WHERE code=?");
    $stmt->bind_param("s", $code);
    $res = $stmt->execute();
    if (!$res) {
        $conn->rollback();
        return InsertUserError::DatabaseError;
    }

    $count = $stmt->get_result()->fetch_assoc()["count"];
    if ($count > 0) {
        $conn->rollback();
        return InsertEmployeeError::CodeAlreadyInUse;
    }

    $stmt = $conn->prepare("INSERT INTO Employee (user_id, code, role, address_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("issi", $user_id, $code, $role, $address_id);
    $res = $stmt->execute();

    if (!$res) {
        $conn->rollback();
        return InsertUserError::DatabaseError;
    }

    $conn->commit();
    return $user_id;
}
?>
