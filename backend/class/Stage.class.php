<?php

require_once __DIR__ . '/../api/db.php';

class Stage {
  private $conn;

  public function __construct() {
      $this->conn = ConnectToDB();
  }

  private function json($data) {
      return json_encode($data, JSON_UNESCAPED_UNICODE);
  }

  public function getAllStages() {
      $stmt = $this->conn->query("SELECT * FROM stage ORDER BY order_index ASC");
      $stages = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $this->json($stages);
  }

  public function getStage($id) {
      $stmt = $this->conn->prepare("SELECT * FROM stage WHERE id = :id");
      $stmt->bindParam(':id', $id, PDO::PARAM_INT);
      $stmt->execute();

      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      if ($row) {
          return $this->json($row);
      } else {
          http_response_code(404);
          return $this->json(["error" => "Stage not found"]);
      }
  }

  public function createStage($code, $name, $order_index) {
    try {
        $stmt = $this->conn->prepare("
            INSERT INTO stage (code, name, order_index)
            VALUES (:code, :name, :order_index)
        ");
        $stmt->bindParam(':code', $code);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':order_index', $order_index, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return $this->json([
                "message" => "Stage created",
                "id" => $this->conn->lastInsertId()
            ]);
        }
    } catch (PDOException $e) {
        // Spracovanie chyby
        http_response_code(400); // Nastav HTTP kód na 400 (Bad Request)
        return $this->json(["error" => "Failed to create stage", "details" => $e->getMessage()]);
    }
  }

  public function updateStage($id, $code, $name, $order_index) {
      $stmt = $this->conn->prepare("
          UPDATE stage
          SET code = :code,
              name = :name,
              order_index = :order_index
          WHERE id = :id
      ");
      $stmt->bindParam(':code', $code);
      $stmt->bindParam(':name', $name);
      $stmt->bindParam(':order_index', $order_index, PDO::PARAM_INT);
      $stmt->bindParam(':id', $id, PDO::PARAM_INT);

      if ($stmt->execute()) {
          if ($stmt->rowCount() > 0) {
              return $this->json(["message" => "Stage updated", "id" => $id]);
          } else {
              http_response_code(404);
              return $this->json(["error" => "Stage not found"]);
          }
      } else {
          http_response_code(500);
          return $this->json(["error" => "Failed to update stage"]);
      }
  }

  public function deleteStage($id) {
      $stmt = $this->conn->prepare("DELETE FROM stage WHERE id = :id");
      $stmt->bindParam(':id', $id, PDO::PARAM_INT);

      if ($stmt->execute()) {
          if ($stmt->rowCount() > 0) {
              return $this->json(["message" => "Stage deleted"]);
          } else {
              http_response_code(404);
              return $this->json(["error" => "Stage not found"]);
          }
      } else {
          http_response_code(500);
          return $this->json(["error" => "Failed to delete stage"]);
      }
  }
}

?>