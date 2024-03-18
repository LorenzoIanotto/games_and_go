<?php
class VatNumber {
    private $value = null;
    private function __construct(string $value) {
        $this->value = $value;
    }

    public static function try_from(string $str): VatNumber|false {
        // Every VAT is at least 8 characters in the EU
        if (strlen($str) < 8) {
            return false;
        }

        return new VatNumber($str);
    }

    public function value(): string {
        return $this->value;
    }
}

function insert_vendor(VatNumber $vat, string $business_name, string $email, int $address_id): int|false {
    $conn = DatabaseConnection::get_instance();
    $stmt = $conn->prepare("INSERT INTO Vendor (vat_number, business_name, email, address_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $vat->value(), $business_name, $email, $address_id);

    if (!$stmt->execute()) return false;

    return $conn->insert_id;
}
?>
