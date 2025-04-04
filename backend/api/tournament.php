<?php

function createTournament() {
    $pdo = db();
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['name'], $input['year'], $input['city_id'], $input['date'])) {
        http_response_code(400);
        return ['error' => 'Missing required fields'];
    }

    $stmt = $pdo->prepare("INSERT INTO tournament (name, year, city_id, date) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $input['name'],
        $input['year'],
        $input['city_id'],
        $input['date']
    ]);

    return ['result' => 'Tournament created successfully'];
}

function listTournaments() {
    $pdo = db();
    $stmt = $pdo->query("SELECT id, name, year FROM tournament ORDER BY year DESC, name ASC");
    return ['tournaments' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
}
