<?php
require_once "db.php";

class Person {
    private $conn;

    public function __construct() {
        $this->conn = ConnectToDB();
    }

    public function getAllPersons() {
        $stmt = $this->conn->prepare("
            SELECT p.*, a.street, a.city, a.postalCode, a.country 
            FROM person p 
            LEFT JOIN address a ON p.address_id = a.id
        ");
        $stmt->execute();
        $people = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $people[] = $this->formatPerson($row);
        }

        return json_encode($people);
    }

    public function getPerson($id) {
        $stmt = $this->conn->prepare("
            SELECT p.*, a.street, a.city, a.postalCode, a.country 
            FROM person p 
            LEFT JOIN address a ON p.address_id = a.id
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return json_encode($this->formatPerson($row));
        } else {
            http_response_code(404);
            return json_encode(["error" => "Person not found"]);
        }
    }

    public function createPerson($data) {
        if (!isset($data['firstName'], $data['lastName'], $data['email'], $data['address'])) {
            http_response_code(400);
            return json_encode(["error" => "Missing required fields"]);
        }

        try {
            // 1. Vložiť adresu
            $addr = $data['address'];
            $stmt = $this->conn->prepare("
                INSERT INTO address (street, city, postalCode, country) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $addr['street'],
                $addr['city'],
                $addr['postalCode'],
                $addr['country']
            ]);
            $address_id = $this->conn->lastInsertId();

            // 2. Vložiť osobu
            $stmt = $this->conn->prepare("
                INSERT INTO person (firstName, lastName, email, address_id) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['firstName'],
                $data['lastName'],
                $data['email'],
                $address_id
            ]);

            return json_encode(["message" => "Person created", "id" => $this->conn->lastInsertId()]);
        } catch (PDOException $e) {
            http_response_code(500);
            return json_encode(["error" => "DB error: " . $e->getMessage()]);
        }
    }

    public function updatePerson($id, $data) {
        // TODO: doplniť logiku pre aktualizáciu osoby + adresy
        http_response_code(501);
        return json_encode(["error" => "Not implemented"]);
    }

    public function deletePerson($id) {
        // 1. Získať adresu
        $stmt = $this->conn->prepare("SELECT address_id FROM person WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            http_response_code(404);
            return json_encode(["error" => "Person not found"]);
        }

        $address_id = $result['address_id'];

        // 2. Vymazať osobu
        $stmt = $this->conn->prepare("DELETE FROM person WHERE id = ?");
        $stmt->execute([$id]);

        // 3. Vymazať adresu
        $stmt = $this->conn->prepare("DELETE FROM address WHERE id = ?");
        $stmt->execute([$address_id]);

        return json_encode(["message" => "Person deleted"]);
    }

    private function formatPerson($row) {
        return [
            "id" => (int)$row["id"],
            "firstName" => $row["firstName"],
            "lastName" => $row["lastName"],
            "email" => $row["email"],
            "address" => [
                "street" => $row["street"],
                "city" => $row["city"],
                "postalCode" => $row["postalCode"],
                "country" => $row["country"]
            ]
        ];
    }
}
