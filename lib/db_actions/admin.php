<?php
require_once (__DIR__ . "/../db.php");
require_once (__DIR__ . "/user.php");

function insert_admin(
    string $email,
    string $password,
    string $name,
    string $surname,
    DateTime $birth_date,
    string $gender,
    PhoneNumber $phone_number
): int|InsertUserError {
    $conn = DatabaseConnection::get_instance();
    $conn->begin_transaction();

    $user_id = insert_user($email, $password, $name, $surname, $birth_date, $gender, $phone_number);
    if (!is_numeric($user_id)) {
        $conn->rollback();
        return $user_id;
    }
    
    $stmt = $conn->prepare("INSERT INTO Admin (user_id) VALUES (?)");
    $stmt->bind_param("i", $user_id);
    $res = $stmt->execute();
    if (!$res) {
        $conn->rollback();
        return InsertUserError::DatabaseError;
    }

    $conn->commit();
    return $user_id;
}
?>
