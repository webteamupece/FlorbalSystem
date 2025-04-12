<?php

 require_once __DIR__ . '/../api/db.php';
    define ('MAX_PLAYERS', 12); // Define the maximum number of players allowed in a roster
 class PlayerRoster{
    private $conn;

    public function __construct() {
        $this->conn = ConnectToDB();
    }

    private function json($data) {
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public function getAllPlayerRosters() {
        $sql = "
            SELECT 
                pr.player_id, 
                p.first_name AS player_first_name, 
                p.last_name AS player_last_name, 
                pr.roster_id, 
                r.name AS roster_name
            FROM 
                player_roster pr
            JOIN 
                player p ON pr.player_id = p.id
            JOIN 
                roster r ON pr.roster_id = r.id
        ";

        $stmt = $this->conn->query($sql);
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

    public function getAvailableRostersForPlayer($playerId,$maxPlayers = MAX_PLAYERS) {
        $sql = "
            SELECT r.id, r.name
            FROM roster r
            WHERE r.id NOT IN (
                SELECT pr.roster_id 
                FROM player_roster pr 
                WHERE pr.player_id = :player_id_1
            )
            AND (
                SELECT COUNT(*) 
                FROM player_roster pr2 
                WHERE pr2.roster_id = r.id
            ) < :max_players
            AND r.tournament_id NOT IN (
                SELECT r2.tournament_id
                FROM player_roster pr3
                JOIN roster r2 ON pr3.roster_id = r2.id
                WHERE pr3.player_id = :player_id_2
            )
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':player_id_1', $playerId, PDO::PARAM_INT);
        $stmt->bindValue(':player_id_2', $playerId, PDO::PARAM_INT);
        $stmt->bindValue(':max_players', $maxPlayers, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($result)) {
            return $this->json($result);
        } else {
            http_response_code(404);
            return $this->json(["error" => "No available rosters found"]);
        }
    }

    public function getAvailablePlayersForRoster($rosterId) {
        $sql = "
            SELECT p.id, p.first_name, p.last_name
            FROM player p
            WHERE p.id NOT IN (
                SELECT pr.player_id
                FROM player_roster pr
                WHERE pr.roster_id = :roster_id_1
            )
            AND p.id NOT IN (
                SELECT pr.player_id
                FROM player_roster pr
                JOIN roster r ON pr.roster_id = r.id
                WHERE r.tournament_id = (
                    SELECT tournament_id
                    FROM roster
                    WHERE id = :roster_id_2
                )
            )
        ";
    
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':roster_id_1', $rosterId, PDO::PARAM_INT);
        $stmt->bindValue(':roster_id_2', $rosterId, PDO::PARAM_INT);
        $stmt->execute();
    
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        if (!empty($result)) {
            return $this->json($result);
        } else {
            http_response_code(404);
            return $this->json(["error" => "No available players found"]);
        }
    }

    public function getAvailablePlayersForDuel($duelId) {
        $sql = "
            SELECT p.id, p.first_name, p.last_name
            FROM player p
            JOIN player_roster pr ON p.id = pr.player_id
            JOIN roster r ON pr.roster_id = r.id
            JOIN duel d ON r.id IN (d.roster1_id, d.roster2_id)
            WHERE d.id = :duel_id
        ";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':duel_id', $duelId, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($result)) {
            return $this->json($result);
        } else {
            http_response_code(404);
            return $this->json(["error" => "No players found for this duel"]);
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