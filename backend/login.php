<?php

require_once __DIR__ . '/api/db.php';
$conn = ConnectToDB();

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $username = $_POST['username'] ?? '';

    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM user WHERE username = :username AND password = :password");

    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password', $password);

    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        // Valid credentials
        $_SESSION['logged_in'] = true;

        $_SESSION['role'] = $result['role'];

        // Redirect to the originally requested page
        $redirectTo = $_SESSION['redirect_after_login'] ?? 'index.php';
        unset($_SESSION['redirect_after_login']); // clean up
        header("Location: $redirectTo");
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Login</title>
    <link rel="stylesheet" href="main.css">
</head>
<body>
<nav class="navbar">
    <div class="nav-item">

        <a href="/"><h1>TournamentManGer</h1></a>

    </div>

    <div class="nav-item navigation-links">
        <ul class="nav-item">
            <li>
                <a href="#">Turnaje</a>
            </li>
            <li>
                <a href="#">Štatistika</a>
            </li>

            <?php
            if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
                echo' <li><a href="/login">Prihlásiť sa</a>  </li>';

            }else{
                echo' <li><a href="/logout">Odhlásiť sa</a>  </li>';
            }
            ?>

        </ul>
    </div>

    <div class="nav-item logo-container">

        <img src="./UPeCe_logo.png">

    </div>
</nav>
<main>


    <article class="login-container">
        <form method="post">
            <div class="login-row">
                <label for="username">Username</label>
                <input id="username" name="username" type="text">
            </div>
            <div class="login-row">
                <label for="password">Password</label>
                <input id="password" name="password" type="password">
            </div>

            <button type="submit" class="login-btn">Login</button>
        </form>
    </article>


</main>
</body>
</html>
