<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function ConnectToDB()
{
    $hostname = "db";
    $database = "myapp";
    $username = "user";
    $password = "password";

    try {
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $conn = new PDO("mysql:host=$hostname;dbname=$database;charset=utf8mb4", $username, $password,$options);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        echo json_encode(["error" => "DB connection failed: " . $e->getMessage()]);
        exit;
    }
}

