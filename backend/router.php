<?php

require_once __DIR__ . '/api/db.php';
require_once __DIR__ . '/class/City.class.php';
require_once __DIR__ . '/class/Duel.class.php';
require_once __DIR__ . '/class/Goal.class.php';
require_once __DIR__ . '/class/Organization.class.php';
require_once __DIR__ . '/class/Player_roster.class.php';
require_once __DIR__ . '/class/Player.class.php';
require_once __DIR__ . '/class/Roster.class.php';
require_once __DIR__ . '/class/Stage.class.php';
require_once __DIR__ . '/class/Tournament.class.php';


$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    validatePassword();
}

$path = parse_url($uri, PHP_URL_PATH);
$segments = explode('/', trim($path, '/'));


if ($segments[0] === 'api') {
    header("Content-Type: application/json; charset=utf-8");
    switch ($segments[1]) {
        // ----------------------------------------------------------------------------------------------
        case 'city':
            $city = new City();
            switch ($method) {
                case 'GET':
                    if (isset($segments[2])) {
                        echo $city->getCity($segments[2]);
                    } else {
                        echo $city->getAllCities();
                    }
                    break;

                case 'POST':
                    $data = json_decode(file_get_contents("php://input"));
                    if (!isset($data->name) || empty($data->name)) {
                        http_response_code(400);
                        echo json_encode(["error" => "Missing or empty city name"]);
                        return;
                    }

                    echo $city->createCity($data->name);
                    break;

                case 'PUT':
                    if (isset($segments[2])) {
                        $data = json_decode(file_get_contents("php://input"));
                        if (!isset($data->name) || empty($data->name)) {
                            http_response_code(400);
                            echo json_encode(["error" => "Missing or empty city name"]);
                            return;
                        }
                        echo $city->updateCity($segments[2], $data->name);
                    }
                    break;

                case 'DELETE':
                    if (isset($segments[2])) {
                        echo $city->deleteCity($segments[2]);
                    }
                    break;

                default:
                    http_response_code(405);
                    echo json_encode(["error" => "Method not allowed"]);
            }
            break;

        // ----------------------------------------------------------------------------------------------
        case 'duel':
            $duel = new Duel();
            switch ($method) {
                case 'GET':
                    if (isset($segments[2])) {
                        echo $duel->getDuel($segments[2]);
                    } else {
                        echo $duel->getAllDuels();
                    }
                    break;

                case 'POST':
                    $data = json_decode(file_get_contents("php://input"));
                    if (!isset($data->starting_time, $data->state, $data->stage_id, $data->tournament_id, $data->roster1_id, $data->roster2_id)) {
                        http_response_code(400);
                        echo json_encode(["error" => "Missing data"]);
                        return;
                    }
                    echo $duel->createDuel($data->starting_time, $data->state, $data->stage_id, $data->tournament_id, $data->roster1_id, $data->roster2_id);
                    break;

                case 'PUT':
                    if (isset($segments[2])) {
                        $data = json_decode(file_get_contents("php://input"));
                        echo $duel->updateDuel($segments[2], $data->starting_time, $data->state, $data->stage_id, $data->tournament_id, $data->roster1_id, $data->roster2_id);
                    }
                    break;

                case 'DELETE':
                    if (isset($segments[2])) {
                        echo $duel->deleteDuel($segments[2]);
                    }
                    break;
            }
            break;

        // ----------------------------------------------------------------------------------------------
        case 'goal':
            $goal = new Goal();
            switch ($method) {
                case 'GET':
                    if (isset($segments[2]) && isset($segments[3])) {
                        echo $goal->getGoal($segments[2], $segments[3]); // /goal/player_id/duel_id
                    } else {
                        echo $goal->getAllGoals();
                    }
                    break;

                case 'POST':
                    $data = json_decode(file_get_contents("php://input"));
                    echo $goal->createGoal($data->player_id, $data->duel_id, $data->goal_count, $data->own_goal_count);
                    break;

                case 'PUT':
                    if (isset($segments[2]) && isset($segments[3])) {
                        $data = json_decode(file_get_contents("php://input"));
                        echo $goal->updateGoal($segments[2], $segments[3], $data->goal_count, $data->own_goal_count);
                    }
                    break;

                case 'DELETE':
                    if (isset($segments[2]) && isset($segments[3])) {
                        echo $goal->deleteGoal($segments[2], $segments[3]);
                    }
                    break;
            }
            break;

        // ----------------------------------------------------------------------------------------------
        case 'organization':
            $organization = new Organization();
            switch ($method) {
                case 'GET':
                    if (isset($segments[2])) {
                        echo $organization->getOrg($segments[2]);
                    } else {
                        echo $organization->getAllOrgs();
                    }
                    break;

                case 'POST':
                    $data = json_decode(file_get_contents("php://input"));
                    if (!isset($data->short_name, $data->full_name, $data->city_id)) {
                        http_response_code(400);
                        echo json_encode(["error" => "Missing data"]);
                        return;
                    }
                    echo $organization->createOrg($data->short_name, $data->full_name, $data->city_id);
                    break;

                case 'PUT':
                    if (isset($segments[2])) {
                        $data = json_decode(file_get_contents("php://input"));
                        echo $organization->updateOrg($segments[2], $data->short_name, $data->full_name, $data->city_id);
                    }
                    break;

                case 'DELETE':
                    if (isset($segments[2])) {
                        echo $organization->deleteOrg($segments[2]);
                    }
                    break;
            }
            break;

        // ----------------------------------------------------------------------------------------------
        case 'player_roster':
            $pr = new PlayerRoster();

            switch ($method) {
                case 'GET':
                    echo $pr->getAllPlayerRosters(); // no need for ID filtering
                    break;

                case 'POST':
                    $data = json_decode(file_get_contents("php://input"));
                    echo $pr->createPlayerRoster($data->player_id, $data->roster_id);
                    break;

                case 'DELETE':
                    if (isset($segments[2]) && isset($segments[3])) {
                        echo $pr->deletePlayerRoster($segments[2], $segments[3]);
                    }
                    break;
            }

            break;
        // ----------------------------------------------------------------------------------------------
        case 'roster':
            $roster = new Roster();
            switch ($method) {
                case 'GET':
                    if (isset($segments[2])) {
                        echo $roster->getRoster($segments[2]);
                    } else {
                        echo $roster->getAllRosters();
                    }
                    break;

                case 'POST':
                    $data = json_decode(file_get_contents("php://input"));
                    if (!isset($data->name, $data->tournament_id, $data->organization_id)) {
                        http_response_code(400);
                        echo json_encode(["error" => "Missing data"]);
                        return;
                    }
                    echo $roster->createRoster($data->name, $data->tournament_id, $data->organization_id);
                    break;

                case 'PUT':
                    if (isset($segments[2])) {
                        $data = json_decode(file_get_contents("php://input"));
                        echo $roster->updateRoster($segments[2], $data->name, $data->tournament_id, $data->organization_id);
                    }
                    break;

                case 'DELETE':
                    if (isset($segments[2])) {
                        echo $roster->deleteRoster($segments[2]);
                    }
                    break;
            }
            break;
        // ----------------------------------------------------------------------------------------------
        case 'player':
            $player = new Player();
            switch ($method) {
                case 'GET':
                    if (isset($segments[2])) {
                        echo $player->getPlayer($segments[2]);
                    } else {
                        echo $player->getAllPlayers();
                    }
                    break;

                case 'POST':
                    $data = json_decode(file_get_contents("php://input"));
                    echo $player->createPlayer($data->first_name, $data->last_name, $data->jersey_number);
                    break;

                case 'PUT':
                    if (isset($segments[2])) {
                        $data = json_decode(file_get_contents("php://input"));
                        echo $player->updatePlayer($segments[2], $data->first_name, $data->last_name, $data->jersey_number);
                    }
                    break;

                case 'DELETE':
                    if (isset($segments[2])) {
                        echo $player->deletePlayer($segments[2]);
                    }
                    break;
            }
            break;

        // ----------------------------------------------------------------------------------------------
        case 'stage':
            $stage = new Stage();
            switch ($method) {
                case 'GET':
                    if (isset($segments[2])) {
                        echo $stage->getStage($segments[2]);
                    } else {
                        echo $stage->getAllStages();
                    }
                    break;

                case 'POST':
                    $data = json_decode(file_get_contents("php://input"));
                    echo $stage->createStage($data->code, $data->name, $data->order_index);
                    break;

                case 'PUT':
                    if (isset($segments[2])) {
                        $data = json_decode(file_get_contents("php://input"));
                        echo $stage->updateStage($segments[2], $data->code, $data->name, $data->order_index);
                    }
                    break;

                case 'DELETE':
                    if (isset($segments[2])) {
                        echo $stage->deleteStage($segments[2]);
                    }
                    break;
            }
            break;
        // ----------------------------------------------------------------------------------------------
        case 'tournament':
            $tournament = new Tournament();
            switch ($method) {
                case 'GET':
                    if (isset($segments[2])) {
                        echo $tournament->getTournament($segments[2]);
                    } else {
                        echo $tournament->getAllTournaments();
                    }
                    break;

                case 'POST':
                    $data = json_decode(file_get_contents("php://input"));
                    echo $tournament->createTournament(
                        $data->name,
                        $data->year,
                        $data->host_city_id,
                        $data->date
                    );
                    break;

                case 'PUT':
                    if (isset($segments[2])) {
                        $data = json_decode(file_get_contents("php://input"));
                        echo $tournament->updateTournament(
                            $segments[2],
                            $data->name,
                            $data->year,
                            $data->host_city_id,
                            $data->date
                        );
                    }
                    break;

                case 'DELETE':
                    if (isset($segments[2])) {
                        echo $tournament->deleteTournament($segments[2]);
                    }
                    break;
            }

            break;
        // ----------------------------------------------------------------------------------------------
        case 'available_rosters_for_player':
            if ($method === 'GET') {
                if (!isset($segments[2])) {
                    http_response_code(400);
                    echo json_encode(["error" => "Missing player_id"]);
                    exit;
                }
                $playerId = (int)$segments[2];

                $playerRoster = new PlayerRoster();
                echo $playerRoster->getAvailableRostersForPlayer($playerId);
                exit;
            }
        // ----------------------------------------------------------------------------------------------
        case 'available_players_for_roster':
            if ($method === 'GET') {
                if (!isset($segments[2])) {
                    http_response_code(400);
                    echo json_encode(["error" => "Missing roster_id"]);
                    exit;
                }
                $rosterId = (int)$segments[2];

                $playerRoster = new PlayerRoster();
                echo $playerRoster->getAvailablePlayersForRoster($rosterId);
                exit;
            }
        // ----------------------------------------------------------------------------------------------
        case 'available_duels_for_player':
            if ($method === 'GET') {
                if (!isset($segments[2])) {
                    http_response_code(400);
                    echo json_encode(["error" => "Missing player_id"]);
                    exit;
                }
                $playerId = (int)$segments[2];

                $duel = new Duel();
                echo $duel->getAvailableDuelsForPlayer($playerId);
                exit;
            }
        // ----------------------------------------------------------------------------------------------
        case 'available_players_for_duel':
            if ($method === 'GET') {
                if (!isset($segments[2])) {
                    http_response_code(400);
                    echo json_encode(["error" => "Missing duel_id"]);
                    exit;
                }
                $duelId = (int)$segments[2];

                $playerRoster = new PlayerRoster();
                echo $playerRoster->getAvailablePlayersForDuel($duelId);
                exit;
            }
        // ----------------------------------------------------------------------------------------------
        case 'available_rosters_for_tournament':
            if ($method === 'GET') {
                if (!isset($segments[2])) {
                    http_response_code(400);
                    echo json_encode(["error" => "Missing tournament_id"]);
                    exit;
                }
                $tournamentId = (int)$segments[2];

                $roster = new Roster();
                echo $roster->getAvailableRostersForTournament($tournamentId);
                exit;
            }
        // ----------------------------------------------------------------------------------------------
        case 'players_in_roster':
            if ($method === 'GET') {
                if (!isset($segments[2])) {
                    http_response_code(400);
                    echo json_encode(["error" => "Missing player_id"]);
                    exit;
                }
                $playerId = (int)$segments[2];

                $roster = new Roster();
                echo $roster->getPlayersInRoster($playerId);
                exit;
            }
        // ----------------------------------------------------------------------------------------------
        default:
            http_response_code(404);
            echo json_encode(["error" => "Endpoint not found"]);
            return;
    }
} else if ($segments[0] == 'admin') {

   validatePassword();

   if  ($_SESSION['role'] == 'admin'){
       include  'marekovIndex_copy.php';
   }else{
       include  'index.php';
   }


    exit;
}


function validatePassword() {
    session_start();
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {

        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI']; // store original URL

        header("Location: /login");
        exit;
    }
}