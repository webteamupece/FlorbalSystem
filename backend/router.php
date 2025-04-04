<?php

require_once __DIR__ . '/api/db.php';
require_once __DIR__ . '/api/city.php';
require_once __DIR__ . '/api/organization.php';
require_once __DIR__ . '/api/tournament.php';
require_once __DIR__ . '/api/roster.php';
require_once __DIR__ . '/api/player.php';
require_once __DIR__ . '/api/player_roster.php';
require_once __DIR__ . '/api/City.class.php';

/*$routes = [
    '/api/city/create' => 'createCity',
    '/api/city/list' => 'listCities',
    '/api/organization/create' => 'createOrganization',
    '/api/organization/list' => 'listOrganizations',
    '/api/tournament/create' => 'createTournament',
    '/api/tournament/list' => 'listTournaments',
    '/api/roster/create' => 'createRoster',
    '/api/roster/list' => 'listRosters',
    '/api/player/create' => 'createPlayer',
    '/api/player/list' => 'listPlayers',
    '/api/player_roster/add' => 'addPlayerToRoster',
];

$currentUrl = strtok($_SERVER['REQUEST_URI'], '?');
$method = $_SERVER['REQUEST_METHOD'];

header('Content-Type: application/json; charset=utf-8');

if (isset($routes[$currentUrl])) {
    $handler = $routes[$currentUrl];
    $response = $handler();
    echo json_encode($response);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Endpoint not found']);
}*/


header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

$path = parse_url($uri, PHP_URL_PATH);
$segments = explode('/', trim($path, '/'));


if ($segments[1] === 'api' && $segments[2] === 'persons') {
    $person = new Person();

    switch ($method) {
        case 'GET':
            if (isset($segments[3])) {
                echo $person->getPerson($segments[3]);
            } else {
                echo $person->getAllPersons();
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents("php://input"), true);
            echo $person->createPerson($data);
            break;

        case 'PUT':
            if (isset($segments[3])) {
                $data = json_decode(file_get_contents("php://input"), true);
                echo $person->updatePerson($segments[3], $data);
            }
            break;

        case 'DELETE':
            if (isset($segments[3])) {
                echo $person->deletePerson($segments[3]);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(["error" => "Method not allowed"]);
    }
} else if ($segments[0] === 'api' && $segments[1] === 'city') {
    $city = new City();

    switch ($method) {
        case 'GET':
            if (isset($segments[3])) {
                echo $city->getPerson($segments[3]);
            } else {



                echo $city->getAllCities();
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents("php://input"), true);
            echo $city->createPerson($data);
            break;

        case 'PUT':
            if (isset($segments[3])) {
                $data = json_decode(file_get_contents("php://input"), true);
                echo $city->updatePerson($segments[3], $data);
            }
            break;

        case 'DELETE':
            if (isset($segments[3])) {
                echo $city->deletePerson($segments[3]);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(["error" => "Method not allowed"]);
    }
}