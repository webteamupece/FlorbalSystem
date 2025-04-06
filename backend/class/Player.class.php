<?php

require_once __DIR__ . '/../api/db.php';

class Player {
  private $conn;

  public function __construct() {
      $this->conn = ConnectToDB();
  }

  private function json($data) {
      return json_encode($data, JSON_UNESCAPED_UNICODE);
  }

  public function getAllPlayers() {
      $stmt = $this->conn->query("SELECT * FROM player");
      $players = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $this->json($players);
  }

  public function getPlayer($id) {
      $stmt = $this->conn->prepare("SELECT * FROM player WHERE id = :id");
      $stmt->bindParam(':id', $id, PDO::PARAM_INT);
      $stmt->execute();

      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      if ($row) {
          return $this->json($row);
      } else {
          http_response_code(404);
          return $this->json(["error" => "Player not found"]);
      }
  }

  public function createPlayer($first_name, $last_name, $jersey_number) {
      $stmt = $this->conn->prepare("
          INSERT INTO player (first_name, last_name, jersey_number)
          VALUES (:first, :last, :number)
      ");
      $stmt->bindParam(':first', $first_name);
      $stmt->bindParam(':last', $last_name);
      $stmt->bindParam(':number', $jersey_number, PDO::PARAM_INT);

      if ($stmt->execute()) {
          return $this->json([
              "message" => "Player created",
              "id" => $this->conn->lastInsertId()
          ]);
      } else {
          http_response_code(500);
          return $this->json(["error" => "Failed to create player"]);
      }
  }

  public function updatePlayer($id, $first_name, $last_name, $jersey_number) {
      $stmt = $this->conn->prepare("
          UPDATE player
          SET first_name = :first, last_name = :last, jersey_number = :number
          WHERE id = :id
      ");
      $stmt->bindParam(':first', $first_name);
      $stmt->bindParam(':last', $last_name);
      $stmt->bindParam(':number', $jersey_number, PDO::PARAM_INT);
      $stmt->bindParam(':id', $id, PDO::PARAM_INT);

      if ($stmt->execute()) {
          if ($stmt->rowCount() > 0) {
              return $this->json(["message" => "Player updated", "id" => $id]);
          } else {
              http_response_code(404);
              return $this->json(["error" => "Player not found"]);
          }
      } else {
          http_response_code(500);
          return $this->json(["error" => "Failed to update player"]);
      }
  }

  public function deletePlayer($id) {
      $stmt = $this->conn->prepare("DELETE FROM player WHERE id = :id");
      $stmt->bindParam(':id', $id, PDO::PARAM_INT);

      if ($stmt->execute()) {
          if ($stmt->rowCount() > 0) {
              return $this->json(["message" => "Player deleted"]);
          } else {
              http_response_code(404);
              return $this->json(["error" => "Player not found"]);
          }
      } else {
          http_response_code(500);
          return $this->json(["error" => "Failed to delete player"]);
      }
  }
}

?>