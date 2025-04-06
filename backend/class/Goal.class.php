<?php

require_once __DIR__ . '/../api/db.php';

class Goal {
  private $conn;

  public function __construct() {
      $this->conn = ConnectToDB();
  }

  private function json($data) {
      return json_encode($data, JSON_UNESCAPED_UNICODE);
  }

  public function getAllGoals() {
      $stmt = $this->conn->query("SELECT * FROM goal");
      return $this->json($stmt->fetchAll(PDO::FETCH_ASSOC));
  }

  public function getGoal($player_id, $duel_id) {
      $stmt = $this->conn->prepare("
          SELECT * FROM goal WHERE player_id = :pid AND duel_id = :did
      ");
      $stmt->bindParam(':pid', $player_id, PDO::PARAM_INT);
      $stmt->bindParam(':did', $duel_id, PDO::PARAM_INT);
      $stmt->execute();

      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      if ($row) {
          return $this->json($row);
      } else {
          http_response_code(404);
          return $this->json(["error" => "Goal not found"]);
      }
  }

  public function createGoal($player_id, $duel_id, $goal_count, $own_goal_count) {
      $stmt = $this->conn->prepare("
          INSERT INTO goal (player_id, duel_id, goal_count, own_goal_count)
          VALUES (:pid, :did, :g, :o)
      ");
      $stmt->bindParam(':pid', $player_id, PDO::PARAM_INT);
      $stmt->bindParam(':did', $duel_id, PDO::PARAM_INT);
      $stmt->bindParam(':g', $goal_count, PDO::PARAM_INT);
      $stmt->bindParam(':o', $own_goal_count, PDO::PARAM_INT);

      if ($stmt->execute()) {
          return $this->json(["message" => "Goal recorded"]);
      } else {
          http_response_code(500);
          return $this->json(["error" => "Failed to insert goal"]);
      }
  }

  public function deleteGoal($player_id, $duel_id) {
      $stmt = $this->conn->prepare("DELETE FROM goal WHERE player_id = :pid AND duel_id = :did");
      $stmt->bindParam(':pid', $player_id, PDO::PARAM_INT);
      $stmt->bindParam(':did', $duel_id, PDO::PARAM_INT);

      if ($stmt->execute() && $stmt->rowCount() > 0) {
          return $this->json(["message" => "Goal entry deleted"]);
      } else {
          http_response_code(404);
          return $this->json(["error" => "Goal not found"]);
      }
  }
}

?>