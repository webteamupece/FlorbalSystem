<?php 

require_once __DIR__ . '/../api/db.php';

class Roster {
  private $conn;

  public function __construct() {
      $this->conn = ConnectToDB();
  }

  private function json($data) {
      return json_encode($data, JSON_UNESCAPED_UNICODE);
  }

  public function getAllRosters() {
      $stmt = $this->conn->query("SELECT * FROM roster");
      $rosters = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $this->json($rosters);
  }

  public function getRoster($id) {
      $stmt = $this->conn->prepare("SELECT * FROM roster WHERE id = :id");
      $stmt->bindParam(':id', $id, PDO::PARAM_INT);
      $stmt->execute();

      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      if ($row) {
          return $this->json($row);
      } else {
          http_response_code(404);
          return $this->json(["error" => "Roster not found"]);
      }
  }

  public function createRoster($name, $tournament_id, $organization_id) {
      $stmt = $this->conn->prepare("
          INSERT INTO roster (name, tournament_id, organization_id)
          VALUES (:name, :tournament_id, :organization_id)
      ");
      $stmt->bindParam(':name', $name);
      $stmt->bindParam(':tournament_id', $tournament_id, PDO::PARAM_INT);
      $stmt->bindParam(':organization_id', $organization_id, PDO::PARAM_INT);

      if ($stmt->execute()) {
          return $this->json([
              "message" => "Roster created",
              "id" => $this->conn->lastInsertId()
          ]);
      } else {
          http_response_code(500);
          return $this->json(["error" => "Failed to create roster"]);
      }
  }

  public function updateRoster($id, $name, $tournament_id, $organization_id) {
      $stmt = $this->conn->prepare("
          UPDATE roster
          SET name = :name,
              tournament_id = :tournament_id,
              organization_id = :organization_id
          WHERE id = :id
      ");
      $stmt->bindParam(':name', $name);
      $stmt->bindParam(':tournament_id', $tournament_id, PDO::PARAM_INT);
      $stmt->bindParam(':organization_id', $organization_id, PDO::PARAM_INT);
      $stmt->bindParam(':id', $id, PDO::PARAM_INT);

      if ($stmt->execute()) {
          if ($stmt->rowCount() > 0) {
              return $this->json(["message" => "Roster updated", "id" => $id]);
          } else {
              http_response_code(404);
              return $this->json(["error" => "Roster not found"]);
          }
      } else {
          http_response_code(500);
          return $this->json(["error" => "Failed to update roster"]);
      }
  }

  public function deleteRoster($id) {
      $stmt = $this->conn->prepare("DELETE FROM roster WHERE id = :id");
      $stmt->bindParam(':id', $id, PDO::PARAM_INT);

      if ($stmt->execute()) {
          if ($stmt->rowCount() > 0) {
              return $this->json(["message" => "Roster deleted"]);
          } else {
              http_response_code(404);
              return $this->json(["error" => "Roster not found"]);
          }
      } else {
          http_response_code(500);
          return $this->json(["error" => "Failed to delete roster"]);
      }
  }

    public function getAvailableRostersForTournament($tournament_id) {
        $stmt = $this->conn->prepare("
            SELECT * FROM roster
            WHERE tournament_id = :tournament_id
        ");
        $stmt->bindParam(':tournament_id', $tournament_id, PDO::PARAM_INT);
        $stmt->execute();

        $rosters = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $this->json($rosters);
    }

    public function getPlayersInRoster($roster_id) {
        $stmt = $this->conn->prepare("
            SELECT pr.player_id, p.first_name, p.last_name
            FROM player_roster pr
            JOIN player p ON pr.player_id = p.id
            WHERE pr.roster_id = :roster_id
        ");
        $stmt->bindParam(':roster_id', $roster_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $rosters = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $this->json($rosters);
    }

}

?>