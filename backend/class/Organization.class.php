<?php

require_once __DIR__ . '/../api/db.php';

class Organization {
    private $conn;

    public function __construct() {
        $this->conn = ConnectToDB();
    }

    private function json($data) {
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public function getAllOrgs() {
        $stmt = $this->conn->query("SELECT * FROM organization");
        $orgs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $this->json($orgs);
    }

    public function getOrg($id) {
        $stmt = $this->conn->prepare("SELECT * FROM organization WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return $this->json($row);
        } else {
            http_response_code(404);
            return $this->json(["error" => "Organization not found"]);
        }
    }

    public function createOrg($short, $full, $cityId) {
        $stmt = $this->conn->prepare("
            INSERT INTO organization (short_name, full_name, city_id)
            VALUES (:short, :full, :city_id)
        ");
        $stmt->bindParam(':short', $short);
        $stmt->bindParam(':full', $full);
        $stmt->bindParam(':city_id', $cityId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return $this->json([
                "message" => "Organization created",
                "id" => $this->conn->lastInsertId()
            ]);
        } else {
            http_response_code(500);
            return $this->json(["error" => "Failed to create organization"]);
        }
    }

    public function updateOrg($id, $short, $full, $cityId) {
        $stmt = $this->conn->prepare("
            UPDATE organization
            SET short_name = :short, full_name = :full, city_id = :city_id
            WHERE id = :id
        ");
        $stmt->bindParam(':short', $short);
        $stmt->bindParam(':full', $full);
        $stmt->bindParam(':city_id', $cityId, PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                return $this->json(["message" => "Organization updated", "id" => $id]);
            } else {
                http_response_code(404);
                return $this->json(["error" => "Organization not found"]);
            }
        } else {
            http_response_code(500);
            return $this->json(["error" => "Failed to update organization"]);
        }
    }

    public function deleteOrg($id) {
        $stmt = $this->conn->prepare("DELETE FROM organization WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                return $this->json(["message" => "Organization deleted"]);
            } else {
                http_response_code(404);
                return $this->json(["error" => "Organization not found"]);
            }
        } else {
            http_response_code(500);
            return $this->json(["error" => "Failed to delete organization"]);
        }
    }
}

?>