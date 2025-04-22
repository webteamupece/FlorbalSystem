<?php
require_once __DIR__ . '/api/db.php';
require_once __DIR__ . '/class/Duel.class.php';
$conn = ConnectToDB();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Initial</title>
    <link rel="stylesheet" href="/main.css">
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
                echo ' <li><a href="/login">Prihlásiť sa</a>  </li>';

            } else {
                echo ' <li><a href="/logout">Odhlásiť sa</a>  </li>';
            }
            ?>

        </ul>
    </div>

    <div class="nav-item logo-container">

        <img src="./UPeCe_logo.png">

    </div>
</nav>


<main>
    <div class="score-container">

        <?php
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $segments = explode('/', trim($path, '/'));
        $duelId = $segments[1] ?? null;


        $Duel = new Duel();

        // ziska duel z db
        $duel = json_decode($Duel->getDuel($duelId), true);

        if ($duel) {
            $Tournament = new Tournament();
            $tournament = json_decode($Tournament->getTournament($duel['tournament_id']), true);

            echo '<p>' . htmlspecialchars($tournament['name']) . ' </p>';

            $Roster = new Roster();
            $roster1 = json_decode($Roster->getRoster($duel['roster1_id']), true);
            $roster2 = json_decode($Roster->getRoster($duel['roster2_id']), true);
            $roster1_players = json_decode($Roster->getPlayersInRoster($duel['roster1_id']), true);
            $roster2_players = json_decode($Roster->getPlayersInRoster($duel['roster2_id']), true);

            echo '<h1>' . htmlspecialchars($roster1['name']) . ' vs ' . htmlspecialchars($roster2['name']) .' </h1>';

            $score = json_decode($Duel->getScore($duel['id']), true);

            echo '<p class="score-display">' . htmlspecialchars($score['roster1_score']) . ':' .htmlspecialchars($score['roster2_score']) .' </p>';
        }
        ?>

        <div class="player-goal-container">
            <div class="team-goal-container">
                <div class="player-selector">
                    <label for="players">Vyberte hráča:</label>
                    <select id="players" name="players">
                        <option value="player1">Ján Novák</option>
                        <option value="player2">Marek Kováč</option>
                        <option value="player3">Peter Horváth</option>
                        <option value="player4">Michal Farkaš</option>
                        <option value="player5">Tomáš Kříž</option>
                        <option value="player6">Lukáš Černý</option>
                        <option value="player7">Jakub Maček</option>
                        <option value="player8">Róbert Baláž</option>
                        <option value="player9">Viktor Gál</option>
                        <option value="player10">Samuel Šimko</option>
                    </select>
                </div>
                <button>Gól +1</button>
            </div>
            <div class="team-goal-container">
                <div class="player-selector">
                    <label for="players2">Vyberte hráča:</label>
                    <select id="players2" name="players2">
                        <option value="player1">Ján Novák</option>
                        <option value="player2">Marek Kováč</option>
                        <option value="player3">Peter Horváth</option>
                        <option value="player4">Michal Farkaš</option>
                        <option value="player5">Tomáš Kříž</option>
                        <option value="player6">Lukáš Černý</option>
                        <option value="player7">Jakub Maček</option>
                        <option value="player8">Róbert Baláž</option>
                        <option value="player9">Viktor Gál</option>
                        <option value="player10">Samuel Šimko</option>
                    </select>
                </div>
                <button>Gól +1</button>
            </div>
        </div>
        <button>Upraviť stav</button>
    </div>


</main>


</body>
</html>