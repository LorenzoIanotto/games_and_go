<?php
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

class LoyalityCard {
    public $number = null;
    public $points = 0;

    public function __construct(int $number, int $points) {
        $this->number = $number;
        $this->points = $points;
    }
}

// null is returned when there's no loyality card, false when there's an error
function get_loyality_card(int $user_id): LoyalityCard|null|false {
    $conn = DatabaseConnection::get_instance();
    $stmt = $conn->prepare("SELECT loyality_card_number, loyality_card_points FROM Customer WHERE user_id=?");
    $stmt->bind_param("i", $user_id);
    if (!$stmt->execute()) return false;

    if ($row = $stmt->get_result()->fetch_assoc()) {
        return $row["loyality_card_number"] !== null ? new LoyalityCard($row["loyality_card_number"], $row["loyality_card_points"]) : null;
    } else {
        return false;
    }
}

function add_points_to_customer(int $customer_id, int $points): bool {
    $loyality_card = get_loyality_card($customer_id);
    if (!$loyality_card) return false;

    $conn = DatabaseConnection::get_instance();
    $stmt = $conn->prepare("UPDATE Customer SET loyality_card_points=loyality_card_points+? WHERE user_id=?");
    $stmt->bind_param("ii", $points, $customer_id);
    if(!$stmt->execute()) return false;

    return true;
}
