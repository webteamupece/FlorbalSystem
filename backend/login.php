<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $username = $_POST['username'] ?? '';

    if ($password === 'admin123' && $username === 'admin') {
        $_SESSION['logged_in'] = true;

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

        <h1>TournamentManGer</h1>

    </div>

    <div class="nav-item navigation-links">
        <ul class="nav-item">
            <li>
                <a href="#">Turnaje</a>
            </li>
            <li>
                <a href="#">Štatistika</a>
            </li>
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
