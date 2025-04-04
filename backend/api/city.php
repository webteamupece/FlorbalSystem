<?php

function createCity() {
    $pdo = db();
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['name']) || empty(trim($input['name']))) {
        http_response_code(400);
        return ['error' => 'City name is required'];
    }

    $stmt = $pdo->prepare("INSERT INTO city (name) VALUES (?)");
    $stmt->execute([$input['name']]);

    return ['result' => 'City created successfully'];
}

function listCities() {
    $pdo = db();
    $stmt = $pdo->query("SELECT id, name FROM city ORDER BY name");
    return ['cities' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
}
