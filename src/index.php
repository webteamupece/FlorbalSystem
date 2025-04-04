<?php

$routes = [
    '/api/city/create' => 'createCity',
];

// ✅ MySQL connection
function db() {
    $host = 'db';
    $db   = 'myapp';
    $user = 'user';
    $pass = 'password';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    try {
        $pdo = new PDO($dsn, $user, $pass);
        return $pdo;
    } catch (\PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed']);
        exit;
    }
}

// ✅ Create city function
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






// toto nemenit to spusta funkcie podla mena z $routes
// 🧭 Routing
$currentUrl = strtok($_SERVER['REQUEST_URI'], '?');

if (isset($routes[$currentUrl])) {
    $handler = $routes[$currentUrl];
    $response = $handler();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Endpoint not found']);
}
