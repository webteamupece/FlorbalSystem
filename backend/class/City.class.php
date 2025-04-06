<?php

require_once __DIR__ . '/../api/db.php';

class City {
    private $conn;

    public function __construct() {
        $this->conn = ConnectToDB();
    }

    public function getAllCities() {
        $stmt = $this->conn->query("SELECT id, name FROM city");
        $cities = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return json_encode($cities, JSON_UNESCAPED_UNICODE);
    }

    public function getCity($id) {
        $stmt = $this->conn->prepare("SELECT id, name FROM city WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($row) {
            return json_encode($row, JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(404);
            return json_encode(["error" => "City not found"]);
        }
    }
    

    public function createCity($name) {
        $stmt = $this->conn->prepare("INSERT INTO city (name) VALUES (:name)");
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        if ($stmt->execute()) {
            return json_encode([
                "message" => "City created",
                "id" => $this->conn->lastInsertId()
            ], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(500);
            return json_encode(["error" => "Failed to create city"]);
        }
    }

    public function updateCity($id, $name) {
        $stmt = $this->conn->prepare("UPDATE city SET name = :name WHERE id = :id");
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                return json_encode([
                    "message" => "City updated",
                    "id" => $id
                ], JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(404);
                return json_encode(["error" => "City not found"], JSON_UNESCAPED_UNICODE);
            }
        } else {
            http_response_code(500);
            return json_encode(["error" => "Failed to update city"], JSON_UNESCAPED_UNICODE);
        }
    }

    public function deleteCity($id) {
        $stmt = $this->conn->prepare("DELETE FROM city WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                return json_encode(["message" => "City deleted"], JSON_UNESCAPED_UNICODE);

            } else {
                http_response_code(404);
                return json_encode(["error" => "City not found"], JSON_UNESCAPED_UNICODE);
            }
        } else {
            http_response_code(500);
            return json_encode(["error" => "Failed to delete city"], JSON_UNESCAPED_UNICODE);
        }
    }
}
