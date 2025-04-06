<?php
    require_once __DIR__ . '/api/db.php';
    $pdo = ConnectToDB();
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tables as $table) {
        echo "<option value=\"$table\">$table</option>";
    }

    function handleCrud($instance, $segments, $method, $fieldMap) {
        $id = $segments[2] ?? null;
    
        switch ($method) {
            case 'GET':
                echo $id ? $instance->{"get" . ucfirst($fieldMap['entity'])}($id)
                         : $instance->{"getAll" . ucfirst($fieldMap['entityPlural'])}();
                break;
    
            case 'POST':
                $data = json_decode(file_get_contents("php://input"));
                foreach ($fieldMap['create'] as $field) {
                    if (!isset($data->$field)) {
                        http_response_code(400);
                        echo json_encode(["error" => "Missing field: $field"]);
                        return;
                    }
                }
                $params = array_map(fn($f) => $data->$f, $fieldMap['create']);
                echo call_user_func_array([$instance, "create" . ucfirst($fieldMap['entity'])], $params);
                break;
    
            case 'PUT':
                if ($id) {
                    $data = json_decode(file_get_contents("php://input"));
                    $params = array_map(fn($f) => $data->$f ?? null, $fieldMap['update']);
                    array_unshift($params, $id);
                    echo call_user_func_array([$instance, "update" . ucfirst($fieldMap['entity'])], $params);
                }
                break;
    
            case 'DELETE':
                if ($id) {
                    echo $instance->{"delete" . ucfirst($fieldMap['entity'])}($id);
                }
                break;
    
            default:
                http_response_code(405);
                echo json_encode(["error" => "Method not allowed"]);
        }
    }


    
    case 'tournament':
        $t = new Tournament();
        handleCrud($t, $segments, $method, [
            'entity' => 'tournament',
            'entityPlural' => 'tournaments',
            'create' => ['name', 'year', 'host_city_id', 'date'],
            'update' => ['name', 'year', 'host_city_id', 'date']
        ]);
        break;

        
        $data = json_decode(file_get_contents("php://input"));
        if (!isset($data->name) || empty($data->name)) {
            http_response_code(400);
            echo json_encode(["error" => "Missing or empty city name"]);
            return;
        }

        
        function validateFields($data, $fields) {
            foreach ($fields as $field) {
                if (!isset($data->$field) || $data->$field === '') {
                    http_response_code(400);
                    echo json_encode(["error" => "Missing or empty field: $field"]);
                    exit;
                }
            }
        }


        $methodName = "getAll" . ucfirst($fieldMap['entityPlural']);
        echo $instance->$methodName();
                

?>

