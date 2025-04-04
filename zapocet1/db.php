<?php
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);

function ConnectToDB() {
  $hostname = "localhost";
  $database = "zapocet";
  $username = "xkormos";
  $password = "pass";

  try {
      $conn = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
      $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      return $conn;
  } catch(PDOException $e) {
      echo json_encode(["error" => "DB connection failed: " . $e->getMessage()]);
      exit;
  }
}

?>