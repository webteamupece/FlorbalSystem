<?php

function createPlayer() {
    $pdo = db();
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['player_first_name'], $input['player_last_name'], $input['jersey_number'])) {
        http_response_code(400);
        return ['error' => 'Missing required fields'];
    }

    $stmt = $pdo->prepare("INSERT INTO player (player_first_name, player_last_name, jersey_number) VALUES (?, ?, ?)");
    $stmt->execute([
        $input['player_first_name'],
        $input['player_last_name'],
        $input['jersey_number']
    ]);

    return ['result' => 'Player created successfully'];
}

function listPlayers() {
    $pdo = db();
    $stmt = $pdo->query("SELECT id, player_first_name, player_last_name, jersey_number FROM player ORDER BY player_last_name, player_first_name");
    return ['players' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
}