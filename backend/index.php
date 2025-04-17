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
            <li>
                <a href="#">IDK este nieco</a>
            </li>

        </ul>
    </div>

    <div class="nav-item logo-container">

        <img src="UPeCe_logo.png">

    </div>
</nav>
<main class="table-main">
    <article class="table-container" id="current-dueles-container">

        <!--<h3>UpeCe florbalový turnaj 2025</h3>-->
        <select class="tournament-select" id="tournament-select">
            <?php
            $stmt = $conn->query("SELECT id, name FROM tournament ORDER BY date DESC");
            $tournaments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($tournaments as $tournament) {
                $id = htmlspecialchars($tournament['id']);
                $name = htmlspecialchars($tournament['name']);
                echo "<option value=\"$id\">$name</option>";
            }
            ?>
        </select>
        <h2>Aktuálne zápasy:</h2>

        <table class="duel-table">
            <thead>
            <tr>
                <th class="time">Čas</th>
                <th class="group">Fáza</th>
                <th class="team-1">Tím 1</th>
                <th class="team-2">Tím 2</th>
                <th class="score">Skóre</th>
                <th class="state">Stav</th>
            </tr>
            </thead>
            <tbody>


            <?php


            $Duel = new Duel(); // instantiate the class

            $currentTournament = $tournaments[0]; // assumes $tournaments is already defined

            $duels = $Duel->getAllTournamentDuelsWithStages($currentTournament['id']); // correct method call

            $duels = json_decode($duels, true);
            foreach ($duels as $duel) {
                $time = !empty($duel['starting_time']) ? date('H:i', strtotime($duel['starting_time'])) : 'GG';
                echo "<tr>";
                echo "<td class=\"time\">" . htmlspecialchars($time) . "</td>";
                echo "<td class=\"group\">" . htmlspecialchars($duel['group'] ?? 'A') . "</td>";
                echo "<td class=\"team-1\">" . htmlspecialchars($duel['roster1_name']) . "</td>";
                echo "<td class=\"team-2\">" . htmlspecialchars($duel['roster2_name']) . "</td>";
                echo "<td class=\"score\">" . htmlspecialchars($duel['roster1_score']) . ':' . htmlspecialchars($duel['roster2_score'] ?? 'GG') . "</td>";
                echo "<td class=\"state\">" . htmlspecialchars($duel['state'] ?? 'Upcoming') . "</td>";
                echo "</tr>";
            }

            ?>

            </tbody>
        </table>
    </article>
</main>

<script>
    // Funkcia na načítanie všetkých turnajov
    function loadTournaments() {
        fetch('/api/tournament')
            .then(response => response.json())
            .then(data => {
                const tournamentSelect = document.getElementById('tournament-select');


                data.forEach(tournament => {
                    const option = document.createElement('option');
                    option.value = tournament.id;
                    option.textContent = `${tournament.name}`;
                    tournamentSelect.appendChild(option);
                });
            })
            .catch(err => {
                console.error('Chyba pri načítaní turnajov:', err);
                document.getElementById('output').textContent = 'Chyba pri načítaní turnajov.';
            });
    }

    const formFields = {
        player: [{
            name: 'jersey_number',
            label: 'Číslo dresu',
            type: 'number'
        },
            {
                name: 'first_name',
                label: 'Meno',
                type: 'text'
            },
            {
                name: 'last_name',
                label: 'Priezvisko',
                type: 'text'
            }
        ],
        goal: [{
            name: 'player_id',
            label: 'ID hráča',
            type: 'select',
            optionsEndpoint: '/api/available_players_for_duel',
            optionsEndpointBasic: '/api/player'
        },
            {
                name: 'duel_id',
                label: 'ID zápasu',
                type: 'select',
                optionsEndpoint: '/api/available_duels_for_player',
                optionsEndpointBasic: '/api/duel'
            },
            {
                name: 'goal_count',
                label: 'Góly',
                type: 'number'
            },
            {
                name: 'goal_controller',
                label: 'Góly',
                type: 'button'
            },
            {
                name: 'own_goal_count',
                label: 'Vlastné góly',
                type: 'number'
            },
            {
                name: 'own_goal_controller',
                label: 'Vlastné góly',
                type: 'button'
            }
        ],
        city: [{
            name: 'name',
            label: 'Názov mesta',
            type: 'text'
        }],
        organization: [{
            name: 'short_name',
            label: 'Skrátený názov',
            type: 'text'
        },
            {
                name: 'full_name',
                label: 'Celý názov',
                type: 'text'
            }, {
                name: 'city_id',
                label: 'ID mesta',
                type: 'select',
                optionsEndpoint: '/api/city',
                optionsEndpointBasic: '/api/city'
            }
        ],
        tournament: [{
            name: 'name',
            label: 'Názov turnaja',
            type: 'text'
        },
            {
                name: 'year',
                label: 'Rok',
                type: 'number'
            },
            {
                name: 'host_city_id',
                label: 'ID hostiteľského mesta',
                type: 'select',
                optionsEndpoint: '/api/city',
                optionsEndpointBasic: '/api/city'
            },
            {
                name: 'date',
                label: 'Dátum',
                type: 'date'
            }
        ],
        stage: [{
            name: 'code',
            label: 'Kód fázy',
            type: 'select',
            options: [
                {value: 'group', label: 'Skupinová fáza'},
                {value: 'round_of_sixteen', label: 'Osemfinále'},
                {value: 'quarterfinals', label: 'Štvrťfinále'},
                {value: 'semifinals', label: 'Semifinále'},
                {value: 'third_place', label: 'Zápas o tretie miesto'},
                {value: 'finals', label: 'Finále'},
            ]
        },
            {
                name: 'name',
                label: 'Názov fázy',
                type: 'text'
            },
            {
                name: 'order_index',
                label: 'Poradie',
                type: 'number'
            }
        ],
        duel: [{
            name: 'starting_time',
            label: 'Začiatok zápasu',
            type: 'datetime-local'
        },
            {
                name: 'state',
                label: 'Stav zápasu',
                type: 'select',
                options: [
                    {value: 'SCHEDULED', label: 'Naplánovaný'},
                    {value: 'ONGOING', label: 'Prebiehajúci'},
                    {value: 'FINISHED', label: 'Ukončený'}
                ]
            },
            {
                name: 'stage_id',
                label: 'ID fázy',
                type: 'select',
                optionsEndpoint: '/api/stage',
                optionsEndpointBasic: '/api/stage'
            },
            {
                name: 'tournament_id',
                label: 'ID turnaja',
                type: 'select',
                optionsEndpoint: '/api/torunament',
                optionsEndpointBasic: '/api/tournament',
                rosters: [
                    {
                        name: 'roster1_id',
                        label: 'ID zostavy 1',
                        type: 'select',
                        optionsEndpoint: '/api/roster',
                        optionsEndpointBasic: '/api/roster'
                    },
                    {
                        name: 'roster2_id',
                        label: 'ID zostavy 2',
                        type: 'select',
                        optionsEndpoint: '/api/roster',
                        optionsEndpointBasic: '/api/roster'
                    }
                ]
            }
        ],
        roster: [{
            name: 'name',
            label: 'Názov zostavy',
            type: 'text'
        },
            {
                name: 'tournament_id',
                label: 'ID turnaja',
                type: 'select',
                optionsEndpoint: '/api/tournaments',
                optionsEndpointBasic: '/api/tournament'
            },
            {
                name: 'organization_id',
                label: 'ID organizácie',
                type: 'select',
                optionsEndpoint: '/api/organization',
                optionsEndpointBasic: '/api/organization'
            }
        ],
        player_roster: [{
            name: 'player_id',
            label: 'ID hráča',
            type: 'select',
            optionsEndpoint: '/api/available_players_for_roster',
            optionsEndpointBasic: '/api/player'
        },
            {
                name: 'roster_id',
                label: 'ID zostavy',
                type: 'select',
                optionsEndpoint: '/api/available_rosters_for_player',
                optionsEndpointBasic: '/api/roster'
            }
        ]
    };


    // Pomocná funkcia na vytvorenie select elementu
    function createSelect(field) {

        const select = document.createElement('select');
        select.name = field.name;
        select.required = true;
        select.style.marginBottom = '0.5em';

        // Ak sú definované enum možnosti, pridaj ich
        if (field.options) {
            addDefaultOptions(select);
            field.options.forEach(option => {
                const opt = document.createElement('option');
                opt.value = option.value;
                opt.textContent = option.label;
                select.appendChild(opt);
            });
        }

        return select;
    }

    function addDefaultOptions(select) {
        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.textContent = '--- Vyber ---';
        defaultOption.disabled = true;
        defaultOption.selected = true;
        select.appendChild(defaultOption);
    }

    function addErrorOption(select) {
        const errorOption = document.createElement('option');
        errorOption.value = '';
        errorOption.textContent = 'Žiadne dostupné možnosti';
        errorOption.disabled = true;
        errorOption.selected = true;
        select.appendChild(errorOption);
    }


    // Funkcia pre načítanie všetkých entít pre daný typ do selectu
    function loadEntitiesForSelect(entityType, selectElement) {
        fetch(`/api/${entityType}`)
            .then(res => res.json())
            .then(data => {
                // Ponechaj default option
                const defaultOption = selectElement.options[0];
                selectElement.innerHTML = '';
                selectElement.appendChild(defaultOption);

                // Pridaj každú entitu do selectu
                data.forEach(entity => {
                    const option = document.createElement('option');
                    option.value = entity.id;

                    // Vytvor informačný text pre option podľa typu entity
                    let displayText = `ID ${entity.id}`;

                    if (entity.name) {
                        displayText += ` - ${entity.name}`;
                    } else if (entity.first_name && entity.last_name) {
                        displayText += ` - ${entity.first_name} ${entity.last_name}`;
                    } else if (entity.full_name) {
                        displayText += ` - ${entity.full_name}`;
                    } else if (entity.short_name) {
                        displayText += ` - ${entity.short_name}`;
                    } else if (entity.roster1_id && entity.roster2_id) {
                        displayText += ` - Zápas medzi zostavami ${entity.roster1_id} a ${entity.roster2_id}`;
                    }

                    option.textContent = displayText;
                    selectElement.appendChild(option);
                });
            })
            .catch(err => {
                console.error(`Chyba pri načítaní entít pre ${entityType}:`, err);
                // Ponechaj aspoň default option
                const errorOption = document.createElement('option');
                errorOption.value = '';
                errorOption.textContent = 'Chyba pri načítaní - skús reload';
                errorOption.disabled = true;
                selectElement.innerHTML = '';
                selectElement.appendChild(errorOption);
            });
    }

    // Pomocná funkcia na naplnenie selectu možnosťami
    async function populateSelect(select, data) {

        select.innerHTML = ''; // Vyčisti obsah selectu
        addDefaultOptions(select);

        for (const item of data) {
            const option = document.createElement('option');
            option.value = item.id;

            if (item.roster1_id && item.roster2_id && item.tournament_id) {
                try {
                    const [roster1, roster2, tournament] = await Promise.all([
                        fetch(`/api/roster/${item.roster1_id}`).then(res => res.json()),
                        fetch(`/api/roster/${item.roster2_id}`).then(res => res.json()),
                        fetch(`/api/tournament/${item.tournament_id}`).then(res => res.json())
                    ]);

                    option.textContent = `${item.id} - ${tournament.name}: ${roster1.name} vs ${roster2.name}`;
                } catch (err) {
                    console.error('Chyba pri načítaní názvov:', err);
                    option.textContent = `${item.id} - (Chyba pri načítaní názvov)`;
                }
            } else {
                option.textContent = item.id + " - " + (item.name || item.full_name || item.short_name || `${item.first_name} ${item.last_name}`);
            }

            select.appendChild(option);
        }
    }


</script>

</body>
</html>