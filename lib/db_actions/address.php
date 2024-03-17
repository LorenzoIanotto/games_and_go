<?php
function add_address_to_customer(int $customer_id, int $house_number, string $street, string $city, int $postcode, string $country_code, int $extension = null): int|false {
    // Check if the address already exists, and in that case select the id
    $conn = DatabaseConnection::get_instance();
    $conn->begin_transaction();
    $address_id = get_address_id($house_number, $street, $city, $postcode, $country_code, $extension);

    if ($address_id === false) {
        $conn->rollback();
        return false;
    }

    // Add the address only if it doesn't already exist
    if (!$address_id) {
        $address_id = insert_address($house_number, $street, $city, $postcode, $country_code, $extension);

        if (!$address_id) {
            $conn->rollback();
            return false;
        }
    }

    $stmt = $conn->prepare("INSERT IGNORE INTO CustomerAddress (customer_id, address_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $customer_id, $address_id);
    if (!$stmt->execute()) {
        $conn->rollback();
        return false;
    }

    $conn->commit();

    return $address_id;
}

// false means error, null means address non existent
function get_address_id(int $house_number, string $street, string $city, int $postcode, string $country_code, int $extension = null): int|null|false {
    $conn = DatabaseConnection::get_instance();

    $where_extension = $extension ? "extension=?" : "extension IS NULL";
    $stmt = $conn->prepare("SELECT id FROM Address WHERE $where_extension AND house_number=? AND street=? AND city=? AND postcode=? AND country_code=?");
    if ($extension) {
        $stmt->bind_param("iissis", $extension, $house_number, $street, $city, $postcode, $country_code);
    } else {
        $stmt->bind_param("issis", $house_number, $street, $city, $postcode, $country_code);
    }

    if (!$stmt->execute()) {
        return false;
    }

    $row = $stmt->get_result()->fetch_assoc();

    if (!$row) {
        return null;
    }
    
    return intval($row["id"]);
}

function insert_address(int $house_number, string $street, string $city, int $postcode, string $country_code, int $extension = null): int|false {
    $country_code = strtoupper($country_code);
    $conn = DatabaseConnection::get_instance();

    $value_extension = $extension ? "?" : "NULL";
    $stmt = $conn->prepare("INSERT INTO Address (extension, house_number, street, city, postcode, country_code) VALUES ($value_extension, ?, ?, ?, ?, ?)");

    if ($extension) {
        $stmt->bind_param("iissis", $extension, $house_number, $street, $city, $postcode, $country_code);
    } else {
        $stmt->bind_param("issis", $house_number, $street, $city, $postcode, $country_code);
    }

    if (!$stmt->execute()) {
        return false;
    }

    $address_id = $conn->insert_id;

    return intval($address_id);
}
