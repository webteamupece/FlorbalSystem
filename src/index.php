<?php

$routes = [
    '/api/match' => 'getMatches',
];

function getMatches(){
    $filePath = 'public/data.txt';
    $matches = [];
    $pattern = '/([0-9]+)  (\s([A-Za-z]+\s)+)([0-9]+\s+([A-Za-z]+\s+)+)([0-9]*\.[0-9]+)\s([0-9]*\.[0-9]+) ([0-9]+%) ([0-9]*\.[0-9]+)/';
    if ($file = fopen($filePath, "r")) {
        
        while (($line = fgets($file)) !== false) {
            

            if (preg_match($pattern, $line, $match)) {
                $riadok = $match[1];
                $nazov = $match[2];
                $pocet = $match[4];
                $cena = $match[6];
                $cena_spolu = $match[7];
                $DPH = $match[8];
                $Spolu_s_dph = $match[9];

                $matches[] = [
                    'Riadok' => $riadok,
                    'Nazov' => $nazov,
                    'Mnozstvo' => $pocet,
                    'Jednotkova cena' => $cena,
                    'Cena_spolu' => $cena_spolu,
                    'DPH' => $DPH,
                    'Spolu_s_dph' => $Spolu_s_dph,
                ];
            }
            else{
                $parts = preg_split('/\s+/', $line, 2);

            if (count($parts) >= 2) {
                
                $firstPart = $parts[0];
                $secondPart = $parts[1];

                
                $matches[] = [
                    'Riadok' => $firstPart,
                    'Text' => trim($secondPart)
                ];
            }
            
        }
    }
        return["result" => $matches];
        fclose($file);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to open file'], JSON_UNESCAPED_UNICODE);
        exit;
    }


}

$currentUrl = $_SERVER['REQUEST_URI'];


$currentUrl = strtok($currentUrl, '?');


if (isset($routes[$currentUrl]) && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $handler = $routes[$currentUrl];
    $response = $handler();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Endpoint not found'], JSON_UNESCAPED_UNICODE);
}
