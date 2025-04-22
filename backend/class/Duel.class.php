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
  
    public function getAvailableDuelsForPlayer($player_id) {
        $stmt = $this->conn->prepare("
            SELECT d.*
            FROM duel d
            JOIN roster r1 ON d.roster1_id = r1.id
            JOIN roster r2 ON d.roster2_id = r2.id
            JOIN player_roster pr1 ON pr1.roster_id = r1.id
            JOIN player_roster pr2 ON pr2.roster_id = r2.id
            WHERE pr1.player_id = :player_id_1 OR pr2.player_id = :player_id_2
            GROUP BY d.id
        ");
        $stmt->bindParam(':player_id_1', $player_id, PDO::PARAM_INT);
        $stmt->bindParam(':player_id_2', $player_id, PDO::PARAM_INT);
        $stmt->execute();

        $duels = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($duels) {
            return $this->json($duels);
        } else {
            http_response_code(404);
            return $this->json(["error" => "No duels found for the player"]);
        }
    }
    public function getAllTournamentDuelsWithStages($tournament_id) {
        $stmt = $this->conn->prepare("
            SELECT 
                d.*,
                s.name AS stage_name,
                r1.name AS roster1_name,
                r2.name AS roster2_name,
            
                -- Score for roster1: goals by roster1 + own goals by roster2
                COALESCE(SUM(CASE 
                    WHEN pr.roster_id = d.roster1_id THEN g.goal_count
                    WHEN pr.roster_id = d.roster2_id THEN g.own_goal_count
                    ELSE 0
                END), 0) AS roster1_score,
            
                -- Score for roster2: goals by roster2 + own goals by roster1
                COALESCE(SUM(CASE 
                    WHEN pr.roster_id = d.roster2_id THEN g.goal_count
                    WHEN pr.roster_id = d.roster1_id THEN g.own_goal_count
                    ELSE 0
                END), 0) AS roster2_score
            
            FROM duel d
            JOIN tournament t ON t.id = d.tournament_id
            JOIN stage s ON s.id = d.stage_id
            JOIN roster r1 ON r1.id = d.roster1_id
            JOIN roster r2 ON r2.id = d.roster2_id
            
            -- Join goal and player
            LEFT JOIN goal g ON g.duel_id = d.id
            LEFT JOIN player p ON p.id = g.player_id
            
            -- ✅ Join player_roster to find the player's team
            LEFT JOIN player_roster pr ON pr.player_id = p.id AND r1.id = pr.roster_id OR r2.id = pr.roster_id
            
            WHERE d.tournament_id = :tournament_id
            
            GROUP BY d.id, s.name, r1.name, r2.name
        ");
        $stmt->bindParam(':tournament_id', $tournament_id, PDO::PARAM_INT);
        $stmt->execute();

        $duels = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($duels) {
            return $this->json($duels);
        } else {
            http_response_code(404);
            return $this->json(["error" => "No duels found for the tournament"]);
        }
    }

    public function getScore($duel_id) {
        $stmt = $this->conn->prepare("
            SELECT 
                d.id,
                -- Score for roster1: goals by roster1 + own goals by roster2
                COALESCE(SUM(CASE 
                    WHEN pr.roster_id = d.roster1_id THEN g.goal_count
                    WHEN pr.roster_id = d.roster2_id THEN g.own_goal_count
                    ELSE 0
                END), 0) AS roster1_score,
            
                -- Score for roster2: goals by roster2 + own goals by roster1
                COALESCE(SUM(CASE 
                    WHEN pr.roster_id = d.roster2_id THEN g.goal_count
                    WHEN pr.roster_id = d.roster1_id THEN g.own_goal_count
                    ELSE 0
                END), 0) AS roster2_score
            
            FROM duel d
            JOIN roster r1 ON r1.id = d.roster1_id
            JOIN roster r2 ON r2.id = d.roster2_id
            
            -- Join goal and player
            LEFT JOIN goal g ON g.duel_id = d.id
            LEFT JOIN player p ON p.id = g.player_id
            
            -- ✅ Join player_roster to find the player's team
            LEFT JOIN player_roster pr ON pr.player_id = p.id AND r1.id = pr.roster_id OR r2.id = pr.roster_id
            
            WHERE d.id = :duel_id
            
            GROUP BY d.id
        ");
        $stmt->bindParam(':duel_id', $duel_id, PDO::PARAM_INT);
        $stmt->execute();

        $duels = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($duels) {
            return $this->json($duels);
        } else {
            http_response_code(404);
            return $this->json(["error" => "No duels found for the tournament"]);
        }
    }


    public function changeState($duel_id, $state) {
        // TODO otestovat

        $stmt = $this->conn->prepare("UPDATE duels SET state = :state WHERE duel_id = :duel_id
        ");
        $stmt->bindParam(':duel_id', $duel_id, PDO::PARAM_INT);
        $stmt->bindParam(':state', $state);
        $stmt->execute();

        $duels = $stmt->execute();
        if (!$duels) {
            http_response_code(404);
            return $this->json(["error" => "No duels found for the tournament"]);
        }
    }

}

?>
