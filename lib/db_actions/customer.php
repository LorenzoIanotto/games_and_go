<?php
require_once "../../lib/db.php";
require_once "user.php";

function insert_customer(string $email, string $password, string $name, string $surname, DateTime $birth_date, string $gender, PhoneNumber $phone_number): InsertUserError|int {
    $conn = DatabaseConnection::get_instance();
    $conn->begin_transaction();

    $res = insert_user($email, $password, $name, $surname, $birth_date, $gender, $phone_number);

    if (!is_int($res)) {
        $conn->rollback();
        return $res;
    }

    $id = $res;
    
    $stmt = $conn->prepare("INSERT INTO Customer (user_id) VALUES (?)");
    $stmt->bind_param("i", $id);

    if (!$stmt->execute()) {
        $conn->rollback();
        return InsertUserError::DatabaseError;
    }

    $conn->commit();
    return $id;
}
