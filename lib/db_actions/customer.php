<?php
require_once (__DIR__ . "/user.php");
require_once (__DIR__ . "/../db.php");

function delete_user_feedback(int $customer_id, int $product_id): bool {
    $conn = DatabaseConnection::get_instance();
    $stmt = $conn->prepare("DELETE FROM UserFeedback WHERE customer_id=? AND product_id=?");
    $stmt->bind_param("ii", $customer_id, $product_id);

    if (!$stmt->execute()) return false;

    return true;
}

function insert_user_feedback(string $summary, string $description, int $rating, int $customer_id, int $product_id) {
    $conn = DatabaseConnection::get_instance();
    $stmt = $conn->prepare("
        INSERT INTO UserFeedback (summary, description, rating, customer_id, product_id)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("ssiii", $summary, $description, $rating, $customer_id, $product_id);
    
    if (!$stmt->execute()) return false;

    return true;
}

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

    public static function from_customer_id(int $customer_id): LoyalityCard {
        $random_number = rand(100000000, 99999999);
        $loyality_card_number = $random_number*10+$customer_id;
        return new LoyalityCard($loyality_card_number, 0);
    }
}

// returns the loyality card or false in case of error
function insert_loyality_card(int $customer_id): LoyalityCard|false {
    $conn = DatabaseConnection::get_instance();
    $card = LoyalityCard::from_customer_id($customer_id);

    $stmt = $conn->prepare("UPDATE Customer SET loyality_card_number=? WHERE user_id=?");
    $stmt->bind_param("ii", $card->number, $customer_id);

    if (!$stmt->execute()) {
        return false;
    }

    return $card;
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
