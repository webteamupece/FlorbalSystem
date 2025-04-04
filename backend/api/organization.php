<?php

function createOrganization() {
    $pdo = db();
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['name']) || !isset($input['city_id'])) {
        http_response_code(400);
        return ['error' => 'Missing name or city_id'];
    }

    $stmt = $pdo->prepare("INSERT INTO organization (name, city_id) VALUES (?, ?)");
    $stmt->execute([$input['name'], $input['city_id']]);

    return ['result' => 'Organization created successfully'];
}

function listOrganizations() {
    $pdo = db();
    $stmt = $pdo->query("SELECT id, name FROM organization ORDER BY name");
    return ['organizations' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
}