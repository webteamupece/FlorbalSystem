<?php

require_once __DIR__ . '/../api/db.php';

class Tournament {
  private $conn;

  public function __construct() {
      $this->conn = ConnectToDB();
  }

  private function json($data) {
      return json_encode($data, JSON_UNESCAPED_UNICODE);
  }

  public function getAllTournaments() {
      $stmt = $this->conn->query("SELECT * FROM tournament ORDER BY date DESC");
      $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $this->json($rows);
  }

  public function getTournament($id) {
      $stmt = $this->conn->prepare("SELECT * FROM tournament WHERE id = :id");
      $stmt->bindParam(':id', $id, PDO::PARAM_INT);
      $stmt->execute();

      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      if ($row) {
          return $this->json($row);
      } else {
          http_response_code(404);
          return $this->json(["error" => "Tournament not found"]);
      }
  }

  public function createTournament($name, $year, $host_city_id, $date) {
      $stmt = $this->conn->prepare("
          INSERT INTO tournament (name, year, host_city_id, date)
          VALUES (:name, :year, :host_city_id, :date)
      ");
      $stmt->bindParam(':name', $name);
      $stmt->bindParam(':year', $year, PDO::PARAM_INT);
      $stmt->bindParam(':host_city_id', $host_city_id, PDO::PARAM_INT);
      $stmt->bindParam(':date', $date);

      if ($stmt->execute()) {
          return $this->json([
              "message" => "Tournament created",
              "id" => $this->conn->lastInsertId()
          ]);
      } else {
          http_response_code(500);
          return $this->json(["error" => "Failed to create tournament"]);
      }
  }

  public function updateTournament($id, $name, $year, $host_city_id, $date) {
      $stmt = $this->conn->prepare("
          UPDATE tournament SET
              name = :name,
              year = :year,
              host_city_id = :host_city_id,
              date = :date
          WHERE id = :id
      ");
      $stmt->bindParam(':name', $name);
      $stmt->bindParam(':year', $year, PDO::PARAM_INT);
      $stmt->bindParam(':host_city_id', $host_city_id, PDO::PARAM_INT);
      $stmt->bindParam(':date', $date);
      $stmt->bindParam(':id', $id, PDO::PARAM_INT);

      if ($stmt->execute()) {
          if ($stmt->rowCount() > 0) {
              return $this->json(["message" => "Tournament updated", "id" => $id]);
          } else {
              http_response_code(404);
              return $this->json(["error" => "Tournament not found"]);
          }
      } else {
          http_response_code(500);
          return $this->json(["error" => "Failed to update tournament"]);
      }
  }

  public function deleteTournament($id) {
      $stmt = $this->conn->prepare("DELETE FROM tournament WHERE id = :id");
      $stmt->bindParam(':id', $id, PDO::PARAM_INT);

      if ($stmt->execute()) {
          if ($stmt->rowCount() > 0) {
              return $this->json(["message" => "Tournament deleted"]);
          } else {
              http_response_code(404);
              return $this->json(["error" => "Tournament not found"]);
          }
      } else {
          http_response_code(500);
          return $this->json(["error" => "Failed to delete tournament"]);
      }
  }
  
}

?>