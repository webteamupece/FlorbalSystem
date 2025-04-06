<?php

 require_once __DIR__ . '/../api/db.php';

 class PlayerRoster{
  private $conn;

  public function __construct() {
      $this->conn = ConnectToDB();
  }

  private function json($data) {
      return json_encode($data, JSON_UNESCAPED_UNICODE);
  }

  public function getAllPlayerRosters() {
      $stmt = $this->conn->query("SELECT * FROM player_roster");
      return $this->json($stmt->fetchAll(PDO::FETCH_ASSOC));
  }

  public function createPlayerRoster($player_id, $roster_id) {
      $stmt = $this->conn->prepare("
          INSERT INTO player_roster (player_id, roster_id)
          VALUES (:pid, :rid)
      ");
      $stmt->bindParam(':pid', $player_id, PDO::PARAM_INT);
      $stmt->bindParam(':rid', $roster_id, PDO::PARAM_INT);

      if ($stmt->execute()) {
          return $this->json(["message" => "Player added to roster"]);
      } else {
          http_response_code(500);
          return $this->json(["error" => "Failed to add player to roster"]);
      }
  }

  public function deletePlayerRoster($player_id, $roster_id) {
      $stmt = $this->conn->prepare("
          DELETE FROM player_roster WHERE player_id = :pid AND roster_id = :rid
      ");
      $stmt->bindParam(':pid', $player_id, PDO::PARAM_INT);
      $stmt->bindParam(':rid', $roster_id, PDO::PARAM_INT);

      if ($stmt->execute() && $stmt->rowCount() > 0) {
          return $this->json(["message" => "Player removed from roster"]);
      } else {
          http_response_code(404);
          return $this->json(["error" => "Entry not found"]);
      }
  }

  // TODO:
  // public function getAlltimeGoals($player_id)
  // public function getAlltimeOwnGoals($player_id)
  // public function getGoalsInMatch($player_id, $duel_id)
  // public function getOwnGoalsInMatch($player_id, $duel_id)
  // public function getGoalsInTournament($player_id, $tournament_id)
  // public function getOwnGoalsInTournament($player_id, $tournament_id)
  // public function getRosters($player_id)


}


?>