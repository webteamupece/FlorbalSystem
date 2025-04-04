<?php

function createRoster() {
    $pdo = db();
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['name'], $input['tournament_id'], $input['organization_id'])) {
        http_response_code(400);
        return ['error' => 'Missing required fields'];
    }

    $stmt = $pdo->prepare("INSERT INTO roster (name, tournament_id, organization_id) VALUES (?, ?, ?)");
    $stmt->execute([
        $input['name'],
        $input['tournament_id'],
        $input['organization_id']
    ]);

    return ['result' => 'Roster created successfully'];
}

function listRosters() {
    $pdo = db();
    $stmt = $pdo->query("SELECT id, name FROM roster ORDER BY name ASC");
    return ['rosters' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
}