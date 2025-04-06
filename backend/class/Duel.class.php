<?php 

class Duel {
  private $conn;

  public function __construct() {
      $this->conn = ConnectToDB();
  }

  private function json($data) {
      return json_encode($data, JSON_UNESCAPED_UNICODE);
  }

  public function getAllDuels() {
      $stmt = $this->conn->query("SELECT * FROM duel");
      $duels = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $this->json($duels);
  }

  public function getDuel($id) {
      $stmt = $this->conn->prepare("SELECT * FROM duel WHERE id = :id");
      $stmt->bindParam(':id', $id, PDO::PARAM_INT);
      $stmt->execute();

      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      if ($row) {
          return $this->json($row);
      } else {
          http_response_code(404);
          return $this->json(["error" => "Duel not found"]);
      }
  }

  public function createDuel($starting_time, $state, $stage_id, $tournament_id, $roster1_id, $roster2_id) {
      $stmt = $this->conn->prepare("
          INSERT INTO duel (starting_time, state, stage_id, tournament_id, roster1_id, roster2_id)
          VALUES (:starting_time, :state, :stage_id, :tournament_id, :roster1_id, :roster2_id)
      ");
      $stmt->bindParam(':starting_time', $starting_time);
      $stmt->bindParam(':state', $state);
      $stmt->bindParam(':stage_id', $stage_id, PDO::PARAM_INT);
      $stmt->bindParam(':tournament_id', $tournament_id, PDO::PARAM_INT);
      $stmt->bindParam(':roster1_id', $roster1_id, PDO::PARAM_INT);
      $stmt->bindParam(':roster2_id', $roster2_id, PDO::PARAM_INT);

      if ($stmt->execute()) {
          return $this->json([
              "message" => "Duel created",
              "id" => $this->conn->lastInsertId()
          ]);
      } else {
          http_response_code(500);
          return $this->json(["error" => "Failed to create duel"]);
      }
  }

  public function updateDuel($id, $starting_time, $state, $stage_id, $tournament_id, $roster1_id, $roster2_id) {
      $stmt = $this->conn->prepare("
          UPDATE duel
          SET starting_time = :starting_time,
              state = :state,
              stage_id = :stage_id,
              tournament_id = :tournament_id,
              roster1_id = :roster1_id,
              roster2_id = :roster2_id
          WHERE id = :id
      ");
      $stmt->bindParam(':starting_time', $starting_time);
      $stmt->bindParam(':state', $state);
      $stmt->bindParam(':stage_id', $stage_id, PDO::PARAM_INT);
      $stmt->bindParam(':tournament_id', $tournament_id, PDO::PARAM_INT);
      $stmt->bindParam(':roster1_id', $roster1_id, PDO::PARAM_INT);
      $stmt->bindParam(':roster2_id', $roster2_id, PDO::PARAM_INT);
      $stmt->bindParam(':id', $id, PDO::PARAM_INT);

      if ($stmt->execute()) {
          if ($stmt->rowCount() > 0) {
              return $this->json(["message" => "Duel updated", "id" => $id]);
          } else {
              http_response_code(404);
              return $this->json(["error" => "Duel not found"]);
          }
      } else {
          http_response_code(500);
          return $this->json(["error" => "Failed to update duel"]);
      }
  }

  public function deleteDuel($id) {
      $stmt = $this->conn->prepare("DELETE FROM duel WHERE id = :id");
      $stmt->bindParam(':id', $id, PDO::PARAM_INT);

      if ($stmt->execute()) {
          if ($stmt->rowCount() > 0) {
              return $this->json(["message" => "Duel deleted"]);
          } else {
              http_response_code(404);
              return $this->json(["error" => "Duel not found"]);
          }
      } else {
          http_response_code(500);
          return $this->json(["error" => "Failed to delete duel"]);
      }
  }
}

// TODO:
// public function getScore($duel_id)
// public function changeState($duel_id, $state)


?>