<?php

function addPlayerToRoster() {
    $pdo = db();
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['player_id'], $input['roster_id'])) {
        http_response_code(400);
        return ['error' => 'Missing player_id or roster_id'];
    }

    $stmt = $pdo->prepare("INSERT INTO player_roster (player_id, roster_id) VALUES (?, ?)");
    $stmt->execute([
        $input['player_id'],
        $input['roster_id']
    ]);

    return ['result' => 'Player added to roster'];
}
