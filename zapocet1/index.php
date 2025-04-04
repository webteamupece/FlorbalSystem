<?php

require_once 'db.php';
require_once 'Person.class.php';
require_once 'Address.class.php';


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
}

?>