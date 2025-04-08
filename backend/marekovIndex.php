<?php
require_once __DIR__ . '/api/db.php';
$conn = ConnectToDB();
?>

<!DOCTYPE html>
<html lang="sk">

<head>
    <meta charset="UTF-8">
    <title>Marek the Nebojácny was here</title>
</head>

<body>
    <h2>Získať entitu podľa ID alebo všetky entity</h2>

    <label for="entityType">Typ entity:</label>
    <!-- <select id="entityType">
        <option value="city">Mesto</option>
        <option value="duel">Zápas</option>
        <option value="goal">Gól</option>
        <option value="organization">Organizácia</option>
        <option value="player">Hráč</option>
        <option value="player_roster">Hráči v tíme</option>
        <option value="roster">Organizácia</option>
        <option value="stage">Typ zápasu</option>
        <option value="tournament">Turnaj</option>
    </select> -->

    <select id="entityType">
        <?php
        $stmt = $conn->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            echo "<option value=\"$table\">$table</option>";
        }
        ?>
    </select><br><br>

    <label>ID entity:</label>
    <input type="number" id="entityId" placeholder="Zadaj ID (Nezadávaj žiadne na výpis všetkých prvkov)" style="width: 400px;">
    <button onclick="loadEntityFromDropdown()">Zobraziť entitu</button>
    <button onclick="deleteEntityFromDropdown()">Vymazať entitu</button>

    <h2>Priradiť hráča do tímu (player_roster)</h2>

    <label>Hráč:
        <select id="playerRosterPlayerSelect"></select>
    </label>
    <label>Tím:
        <select id="rosterSelect"></select>
    </label>
    <button onclick="addPlayerToRoster()">➕ Pridať</button>
    <button onclick="loadPlayerRosters()">📄 Zobraziť všetky priradenia</button>

    <h2>Zapísať gól (goal)</h2>

    <label>Hráč:
        <select id="goalPlayerSelect"></select>
    </label>
    <label>Zápas:
        <select id="duelSelect"></select>
    </label>
    <label>Góly: <input type="number" id="goalCount" value="1" min="0"></label>
    <label>Vlastné góly: <input type="number" id="ownGoalCount" value="0" min="0"></label>
    <button onclick="addGoal()">⚽ Zapísať</button>
    <button onclick="loadGoals()">📄 Zobraziť všetky góly</button>

    <div class="section">
        <h2>Vloženie / Úprava údaju</h2>
        <label for="insertAlter">Vyber tabuľku:</label>
        <select id="insertAlter">
            <option value="">-- Vyber --</option>
            <?php
            $stmt = $conn->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

            foreach ($tables as $table) {
                echo "<option value=\"$table\">$table</option>";
            }
            ?>
        </select>
        <button id="reloadDropdowns" style="margin-top: 1em;">🔄 Reload</button>

        <form id="dataForm" style="margin-top: 1em;"></form>
        <!-- ??? -->
        <!-- <label>Góly: 
            <button type="button" onclick="decrementValue('goalCount')">-</button>
            <input type="number" id="goalCount" value="1" min="0" style="width: 50px; text-align: center;">
            <button type="button" onclick="incrementValue('goalCount')">+</button>
        </label>
        <br>
        <label>Vlastné góly: 
            <button type="button" onclick="decrementValue('ownGoalCount')">-</button>
            <input type="number" id="ownGoalCount" value="0" min="0" style="width: 50px; text-align: center;">
            <button type="button" onclick="incrementValue('ownGoalCount')">+</button>
        </label>     -->
        <!-- ???     -->
        <button id="submitBtn" style="display:none;margin-top:1em;" disabled>Odoslať</button>
    </div>

    <pre id="output"></pre>

    <script>
        function getSelectedEntity() {
            return document.getElementById("entityType").value;
        }

        function getEntityId() {
            return document.getElementById("entityId").value;
        }

        function loadEntityFromDropdown() {
            const entity = getSelectedEntity();
            const id = getEntityId();

            if (id.trim()) {
                loadEntity(entity, id);
            } else {
                loadAllEntities(entity);
            }
        }

        function deleteEntityFromDropdown() {
            const entity = getSelectedEntity();
            const id = getEntityId();
            deleteEntity(entity, id); // tiež bola vyššie
        }

        function submitCity(event) {
            event.preventDefault();
            const name = document.getElementById("cityName").value.trim();

            if (!name) {
                document.getElementById("output").textContent = "❌ Zadaj názov mesta.";
                return;
            }

            fetch('api/city', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        name
                    })
                })
                .then(res => res.json().then(json => ({
                    status: res.status,
                    json
                })))
                .then(({
                    status,
                    json
                }) => {
                    if (status >= 200 && status < 300) {
                        document.getElementById("output").textContent = `✅ Mesto vytvorené:\n` + JSON.stringify(json, null, 2);
                    } else {
                        document.getElementById("output").textContent = `❌ Chyba ${status}:\n` + JSON.stringify(json, null, 2);
                    }
                })
                .catch(err => {
                    document.getElementById("output").textContent = `❌ Výnimka v komunikácii:\n${err}`;
                });
        }

        function updateCity() {
            const id = document.getElementById("updateCityId").value;
            const name = document.getElementById("updateCityName").value.trim();

            if (!id || !name) {
                document.getElementById("output").textContent = "❌ Zadaj ID a nový názov mesta.";
                return;
            }

            fetch(`api/city/${id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        name
                    })
                })
                .then(res => res.json())
                .then(data => {
                    document.getElementById("output").textContent = JSON.stringify(data, null, 2);
                })
                .catch(err => {
                    document.getElementById("output").textContent = "❌ Chyba pri úprave: " + err;
                });
        }

        function submitOrg(event) {
            event.preventDefault();
            const short_name = document.getElementById("shortName").value.trim();
            const full_name = document.getElementById("fullName").value.trim();
            const city_id = parseInt(document.getElementById("cityId").value);

            if (!short_name || !full_name || !city_id) {
                document.getElementById("output").textContent = "❌ Vyplň všetky polia.";
                return;
            }

            fetch('api/organization', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        short_name,
                        full_name,
                        city_id
                    })
                })
                .then(res => res.json().then(json => ({
                    status: res.status,
                    json
                })))
                .then(({
                    status,
                    json
                }) => {
                    if (status >= 200 && status < 300) {
                        document.getElementById("output").textContent = `✅ Organizácia vytvorená:\n` + JSON.stringify(json, null, 2);
                    } else {
                        document.getElementById("output").textContent = `❌ Chyba ${status}:\n` + JSON.stringify(json, null, 2);
                    }
                })
                .catch(err => {
                    document.getElementById("output").textContent = `❌ Výnimka:\n${err}`;
                });
        }

        function updateOrganization() {
            const id = document.getElementById("updateOrgId").value;
            const short_name = document.getElementById("updateShort").value.trim();
            const full_name = document.getElementById("updateFull").value.trim();
            const city_id = parseInt(document.getElementById("updateCityId").value);

            if (!id || !short_name || !full_name || !city_id) {
                document.getElementById("output").textContent = "❌ Vyplň všetky polia.";
                return;
            }

            fetch(`api/organization/${id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        short_name,
                        full_name,
                        city_id
                    })
                })
                .then(res => res.json())
                .then(data => {
                    document.getElementById("output").textContent = JSON.stringify(data, null, 2);
                })
                .catch(err => {
                    document.getElementById("output").textContent = "❌ Chyba pri úprave: " + err;
                });
        }
        // ------------------------------------------------------------------------------------
        function loadAllEntities(entity) {
            fetch(`api/${entity}`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById("output").textContent = JSON.stringify(data, null, 2);
                })
                .catch(err => {
                    document.getElementById("output").textContent = `❌ Chyba pri načítaní ${entity}: ` + err;
                });
        }

        function loadEntity(entity, id) {
            if (!id) return;
            fetch(`api/${entity}/${id}`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById("output").textContent = JSON.stringify(data, null, 2);
                })
                .catch(err => {
                    document.getElementById("output").textContent = `❌ Chyba pri načítaní ${entity}: ` + err;
                });
        }

        function deleteEntity(entity, id) {
            if (!id) return;
            fetch(`api/${entity}/${id}`, {
                    method: 'DELETE'
                })
                .then(res => res.json())
                .then(data => {
                    document.getElementById("output").textContent = JSON.stringify(data, null, 2);
                })
                .catch(err => {
                    document.getElementById("output").textContent = `❌ Chyba pri mazaní ${entity}: ` + err;
                });
        }
        // ------------------------------------------------------------------------------------
        async function fetchAndFill(endpoint, selectId, labelCallback) {
            const res = await fetch(`/api/${endpoint}`);
            const data = await res.json();
            const select = document.getElementById(selectId);
            select.innerHTML = '';
            data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = labelCallback(item);
                select.appendChild(option);
            });
        }

        function loadInitialDropdowns() {
            fetchAndFill("player", "playerRosterPlayerSelect", p => `${p.id} - ${p.first_name} ${p.last_name}`);
            fetchAndFill("player", "goalPlayerSelect", p => `${p.id} - ${p.first_name} ${p.last_name}`);
            fetchAndFill("roster", "rosterSelect", r => `${r.id} - ${r.name}`);
            fetchAndFill("duel", "duelSelect", d => `${d.id} - duel`);
        }

        async function addPlayerToRoster() {
            const playerId = document.getElementById("playerRosterPlayerSelect").value;
            const rosterId = document.getElementById("rosterSelect").value;

            const res = await fetch("/api/player_roster", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    player_id: playerId,
                    roster_id: rosterId
                })
            });

            document.getElementById("output").textContent = JSON.stringify(await res.json(), null, 2);
        }

        async function addGoal() {
            const playerId = document.getElementById("goalPlayerSelect").value;
            const duelId = document.getElementById("duelSelect").value;
            const goalCount = parseInt(document.getElementById("goalCount").value);
            const ownGoalCount = parseInt(document.getElementById("ownGoalCount").value);

            const res = await fetch("/api/goal", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    player_id: playerId,
                    duel_id: duelId,
                    goal_count: goalCount,
                    own_goal_count: ownGoalCount
                })
            });

            document.getElementById("output").textContent = JSON.stringify(await res.json(), null, 2);
        }

        async function loadPlayerRosters() {
            const res = await fetch("/api/player_roster");
            document.getElementById("output").textContent = JSON.stringify(await res.json(), null, 2);
        }

        async function loadGoals() {
            const res = await fetch("/api/goal");
            document.getElementById("output").textContent = JSON.stringify(await res.json(), null, 2);
        }

        loadInitialDropdowns();
        // ------------------------------------------------------------------------------------
        const insertAlter = document.getElementById('insertAlter');
        const dataForm = document.getElementById('dataForm');
        const submitBtn = document.getElementById('submitBtn');

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
                        { value: 'group', label: 'Skupinová fáza' },
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
                        { value: 'scheduled', label: 'Naplánovaný' },
                        { value: 'ongoing', label: 'Prebiehajúci' },
                        { value: 'finished', label: 'Ukončený' }
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

        // Pomocná funkcia na vytvorenie input elementu
        function createInput(field) {
            const input = document.createElement('input');
            input.type = field.type;
            input.name = field.name;
            input.required = true;
            input.style.marginBottom = '0.5em';
            return input;
        }

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

        function incrementValue(inputId) {
            const input = document.getElementById(inputId);
            const currentValue = parseInt(input.value) || 0;
            input.value = currentValue + 1;
        }

        function decrementValue(inputId) {
            const input = document.getElementById(inputId);
            const currentValue = parseInt(input.value) || 0;
            if (currentValue > 0) {
                input.value = currentValue - 1;
            }
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

        document.getElementById('reloadDropdowns').addEventListener('click', () => {
            const selectedTable = insertAlter.value; // Ulož aktuálne vybranú tabuľku
            if (selectedTable) {
                insertAlter.value = selectedTable; // Nastav späť vybranú tabuľku
                insertAlter.dispatchEvent(new Event('change')); // Simuluj zmenu
            } else {
                alert('❌ Vyber tabuľku pred reloadom.');
            }
        });


        let isProgrammaticChange = false; // Premenná na sledovanie programovej zmeny
        insertAlter.addEventListener('change', () => {
            let firstSelectedField = null; // Premenná na sledovanie prvého vybraného poľa

            // Počúvaj na zmeny v selecte sekcie na Vloženie/Zmenu údaju
            const selected = insertAlter.value;
            dataForm.innerHTML = '';
            submitBtn.style.display = selected ? 'inline-block' : 'none';

            if (formFields[selected]) {
                // Vytvor formulár na základe vybranej tabuľky
                formFields[selected].forEach((field, index) => {
                    const label = document.createElement('label');
                    label.textContent = field.label + ": ";

                    // Pridaj input podľa typu
                    if (field.type === 'select' && field.optionsEndpoint) {
                        //Ak je field typu SELECT
                        const select = createSelect(field);
                        
                        // Pridaj možnosť "--- Vyber ---"
                        addDefaultOptions(select);

                        // Naplň options fetchom
                        fetch(field.optionsEndpointBasic)
                            .then(res => res.json())
                            .then(data => {
                                populateSelect(select, data);
                            })
                            .catch(err => console.error('Chyba pri fetchnutí možností:', err));
                        
                        if(selected === 'duel' && field.name === 'tournament_id') {
                            select.addEventListener('change', () => {
                                const tournamentId = select.value;
                                const rosterFields = formFields[selected].find(field => field.name === 'tournament_id').rosters;
                                const roster1Field = rosterFields.find(roster => roster.name === 'roster1_id');
                                const roster2Field = rosterFields.find(roster => roster.name === 'roster2_id');

                                let roster1Select = document.querySelector(`select[name="${roster1Field.name}"]`);
                                let roster2Select = document.querySelector(`select[name="${roster2Field.name}"]`);

                                if (!roster1Select) {
                                    roster1Select = createSelect(roster1Field);
                                    dataForm.appendChild(document.createElement('br'));
                                    dataForm.appendChild(document.createTextNode(roster1Field.label + ": "));
                                    dataForm.appendChild(roster1Select);
                                }

                                if (!roster2Select) {
                                    roster2Select = createSelect(roster2Field);
                                    dataForm.appendChild(document.createElement('br'));
                                    dataForm.appendChild(document.createTextNode(roster2Field.label + ": "));
                                    dataForm.appendChild(roster2Select);
                                }

                                if (tournamentId) {
                                    fetch(`/api/available_rosters_for_tournament/${tournamentId}`)
                                        .then(res => res.json())
                                        .then(data => {
                                            populateSelect(roster1Select, data);
                                            populateSelect(roster2Select, data);

                                            // Add event listeners to handle exclusion logic
                                            roster1Select.addEventListener('change', () => {
                                                const selectedRoster1 = roster1Select.value;
                                                const filteredData = data.filter(roster => roster.id.toString() !== selectedRoster1);

                                                // Temporarily remove the event listener to prevent re-fetching
                                                roster2Select.removeEventListener('change', roster2ChangeHandler);
                                                populateSelect(roster2Select, filteredData);
                                                roster2Select.addEventListener('change', roster2ChangeHandler);
                                            });

                                            const roster2ChangeHandler = () => {
                                                const selectedRoster2 = roster2Select.value;
                                                const filteredData = data.filter(roster => roster.id.toString() !== selectedRoster2);

                                                // Temporarily remove the event listener to prevent re-fetching
                                                roster1Select.removeEventListener('change', roster1ChangeHandler);
                                                populateSelect(roster1Select, filteredData);
                                                roster1Select.addEventListener('change', roster1ChangeHandler);
                                            };

                                            roster2Select.addEventListener('change', roster2ChangeHandler);
                                        })
                                        .catch(err => console.error('Chyba pri fetchnutí zostáv:', err));
                                }
                            });
                        }

                        // Pridaj event listener na zmenu vnoreného selectu už pre danú tabuľku   
                        select.addEventListener('change', () => {
                            if (isProgrammaticChange) {
                                isProgrammaticChange = false; // Resetuj stav
                                return; // Preskoč fetch, ak bola zmena programová
                            }

                            // Dynamicky identifikuj druhé pole
                            const otherFieldName = formFields[selected][index === 0 ? 1 : 0].name; // Získaj meno druhého inputu
                            const otherField = formFields[selected].find(field => field.name === otherFieldName);

                            // Ak je toto prvý vybraný select, nastav premennú a nefetchuj
                            if (!firstSelectedField) {
                                firstSelectedField = field.name; // Nastav prvý vybraný select
                            }


                            // Ak toto nie je prvý vybraný select, pokračuj s fetchom
                            if (firstSelectedField == field.name) {
                                // Ak je druhý input už vyplnený, tak fetchni možnosti pre druhý select na základe prvého inputu
                                
                                fetch(otherField.optionsEndpoint + `/${select.value}`)
                                    .then(res => res.json())
                                    .then(data => {
                                        console.log(data);
                                        
                                        // Vyčisti obsah druhého selectu a vlož doň nové možnosti
                                        const select = document.querySelector(`select[name="${otherFieldName}"]`);
                                        if(select === 'goal') {
                                            isProgrammaticChange = true; // Nastav programovú zmenu
                                            document.querySelector('input[name="goal_count"]').value = null;
                                            document.querySelector('input[name="own_goal_count"]').value = null;
                                        }
                                        select.innerHTML = '';
                                        addDefaultOptions(select)

                                        if (data.error) {
                                            addErrorOption(select);
                                        } else {
                                            // Pridaj možnosť pre každý objekt v poli
                                            populateSelect(select, data);
                                        }
                                    })
                                    .catch(err => console.error('Chyba pri fetchnutí možností:', err));
                            }

                            // Ak je vyplnený aj id hráča aj id zápasu fetchni koľko golov a vlastných golov dal a vyplň tieto dáta do inputov ( len pre prípad tabuľky goal)
                            if (selected === 'goal') {
                                const otherFieldSelect = document.querySelector(`select[name="${otherField.name}"]`);
                                
                                if (otherFieldSelect) {
                                    otherFieldSelect.addEventListener('change', () => {
                                        
                                        const playerId = document.querySelector(`select[name="player_id"]`).value ?? null;
                                        const duelId = document.querySelector(`select[name="duel_id"]`).value ?? null;

                                        if (playerId && duelId) {
                                            fetch(`/api/goal/${playerId}/${duelId}`)
                                                .then(res => res.json())
                                                .then(data => {
                                                    const goalCountInput = document.querySelector('input[name="goal_count"]');
                                                    const ownGoalCountInput = document.querySelector('input[name="own_goal_count"]');
                                                    if (!data.error) {
                                                        goalCountInput.value = data.goal_count || 0;
                                                        ownGoalCountInput.value = data.own_goal_count || 0;
                                                    } else {
                                                        goalCountInput.value = null;
                                                        ownGoalCountInput.value = null;
                                                        console.log('Hráč ešte v tomto zápase neskóroval.');
                                                    }
                                                })
                                                .catch(err => console.error('Chyba pri fetchnutí gólov:', err));
                                        }
                                    });
                                }
                            }
                        });

                        dataForm.appendChild(label);
                        dataForm.appendChild(select);

                    } else if (field.type === 'select' && !field.optionsEndpoint) {
                        //Ak je field typu SELECT bez endpointu
                        const select = createSelect(field);

                        dataForm.appendChild(label);
                        dataForm.appendChild(select);

                    } else if (field.type === 'button') {
                        //Ak je field typu BUTTON

                        const incrementButton = document.createElement('button');
                        incrementButton.type = 'button';
                        incrementButton.textContent = '+';
                        incrementButton.style.marginLeft = '0.5em';
                        incrementButton.addEventListener('click', () => {
                            //Pridaj gol
                            const previousFieldName = formFields[selected][index - 1]?.name;
                            document.querySelector(`input[name="${previousFieldName}"]`).value = parseInt(document.querySelector(`input[name="${previousFieldName}"]`).value) + 1;
                            
                            // Zavolaj funkciu na odoslanie formulára
                            handleSubmitForm();
                        });

                        const decrementButton = document.createElement('button');
                        decrementButton.type = 'button';
                        decrementButton.textContent = '-';
                        decrementButton.style.marginLeft = '0.5em';
                        decrementButton.addEventListener('click', () => {
                            //Uber gol
                            const previousFieldName = formFields[selected][index - 1]?.name;
                            document.querySelector(`input[name="${previousFieldName}"]`).value = parseInt(document.querySelector(`input[name="${previousFieldName}"]`).value) - 1;

                            // Zavolaj funkciu na odoslanie formulára
                            handleSubmitForm();
                        });

                        dataForm.appendChild(incrementButton);
                        dataForm.appendChild(decrementButton);
                    } else {
                        //Ak je field typu INPUT
                        const input = createInput(field);
                        dataForm.appendChild(label);
                        dataForm.appendChild(input);
                    }
                    if (!(selected == 'goal' && field.name == 'own_goal_count' || field.name == 'goal_count')) dataForm.appendChild(document.createElement('br'));
                });
                validateForm();
                document.querySelectorAll('#dataForm input, #dataForm select').forEach(field => {
                    field.addEventListener('input', validateForm);
                    field.addEventListener('change', validateForm);
                });
            }
        });

        // Funkcia na kontrolu, či sú všetky povinné polia vyplnené
        function validateForm() {
            const formFields = document.querySelectorAll('#dataForm input, #dataForm select');
            let isValid = true;

            formFields.forEach(field => {
                console.log(field.value);
                
                if (!field.value) {
                    isValid = false;
                }
            });

            // Povoľ alebo zakáž tlačidlo "Odoslať"
            console.log(isValid);
            
            document.getElementById('submitBtn').disabled = !isValid;
        }

        // Pridajte event listener na všetky vstupné polia a selecty
        document.querySelectorAll('#dataForm input, #dataForm select').forEach(field => {
            field.addEventListener('input', validateForm);
            field.addEventListener('change', validateForm);
        });

        // Inicializujte kontrolu pri načítaní stránky
        // document.addEventListener('DOMContentLoaded', validateForm);


        // Funkcia na hlboké porovnanie objektov
        function deepEqual(obj1, obj2) {
            if (typeof obj1 !== 'object' || typeof obj2 !== 'object' || obj1 === null || obj2 === null) {
                return obj1 === obj2;
            }

            const keys1 = Object.keys(obj1);
            const keys2 = Object.keys(obj2);

            if (keys1.length !== keys2.length) {
                return false;
            }

            for (const key of keys1) {
                if (!keys2.includes(key) || !deepEqual(obj1[key], obj2[key])) {
                    return false;
                }
            }

            return true;
        }

        // Funkcia na odoslanie formulára
        async function handleSubmitForm() {
            const formData = new FormData(dataForm);
            const jsonData = Object.fromEntries(formData.entries());
            const selected = insertAlter.value;

            if (selected === 'goal') {
                const playerId = jsonData.player_id;
                const duelId = jsonData.duel_id;

                if (playerId && duelId) {
                    try {
                        // Skontroluj, či existuje záznam pre (player_id, duel_id)
                        const checkResponse = await fetch(`/api/goal/${playerId}/${duelId}`);
                        const checkData = await checkResponse.json();

                        if (checkResponse.ok && !checkData.error) {
                            // Prekonvertuj hodnoty na rovnaké typy
                            checkData.player_id = checkData.player_id.toString();
                            checkData.duel_id = checkData.duel_id.toString();
                            checkData.goal_count = checkData.goal_count.toString();
                            checkData.own_goal_count = checkData.own_goal_count.toString();

                            // Skontroluj, či sa údaje zhodujú
                            if (deepEqual(checkData, jsonData)) {
                                console.log('Údaje sú už aktuálne, nie je potrebná žiadna aktualizácia.');
                                return;
                            }

                            // Ak existuje a údaje sa líšia, použijeme PUT na aktualizáciu
                            const updateResponse = await fetch(`/api/goal/${playerId}/${duelId}`, {
                                method: 'PUT',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify(jsonData)
                            });

                            if (updateResponse.ok) {
                                console.log('Údaj bol aktualizovaný.');
                            } else {
                                console.log('Nastala chyba pri aktualizácii.');
                            }
                        } else {
                            // Ak neexistuje, použijeme POST na vytvorenie
                            const createResponse = await fetch(`/api/goal`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify(jsonData)
                            });

                            if (createResponse.ok) {
                                console.log('Údaj bol uložený.');
                            } else {
                                console.log('Nastala chyba pri ukladaní.');
                            }
                        }
                    } catch (err) {
                        console.error('Chyba pri overovaní alebo ukladaní údajov:', err);
                        console.log('Nastala chyba pri komunikácii so serverom.');
                    }
                } else {
                    console.log('Prosím, vyplňte všetky povinné polia.');
                }
            } else {
                // Pre ostatné tabuľky použijeme POST
                const response = await fetch(`/api/${selected}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(jsonData)
                });

                if (response.ok) {
                    console.log(response);
                    
                    console.log('Údaj bol uložený');
                } else {
                    console.log('Nastala chyba pri ukladaní.');
                }
            }
        }

        // Priradenie funkcie k submit tlačidlu
        submitBtn.addEventListener('click', (e) => {
            e.preventDefault();
            handleSubmitForm();
        });

        
    </script>
</body>

</html>