<?php

require_once __DIR__ . '/api/db.php';
require_once __DIR__ . '/api/city.php';
require_once __DIR__ . '/api/organization.php';
require_once __DIR__ . '/api/tournament.php';
require_once __DIR__ . '/api/roster.php';
require_once __DIR__ . '/api/player.php';
require_once __DIR__ . '/api/player_roster.php';

$routes = [
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
}