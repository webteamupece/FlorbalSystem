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
    <h2>Nastaviť heslo pre API volania</h2>
    <label for="apiPassword">Heslo:</label>
    <input type="password" id="apiPassword" placeholder="Zadaj heslo">
    <button onclick="setPassword()">Použiť heslo</button>
    <p id="passwordStatus" style="color: green;"></p>

    <h2>Získať entitu podľa ID alebo všetky entity</h2>

    <label for="entityType">Typ entity:</label>
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

    
    <h2>Vytvoriť nové mesto</h2>
    <div style="display: flex; justify-content: space-between">
    <form id="cityForm" onsubmit="submitCity(event)" novalidate>
        <label>Názov mesta: <input type="text" id="cityName" required></label><br><br>
        <button type="submit">Odoslať</button>
    </form>
    </div>
    <h2>Zobraziť všetky mestá</h2>
    <button onclick="loadAllEntities('organization')">Zobraziť mestá</button>

    <h2>Vyhľadať mesto podľa ID</h2>
    <input type="number" id="getCityId" placeholder="Zadaj ID">
    <button onclick="loadCity()">Zobraziť mesto</button>

    <h2>Vymazať mesto podľa ID</h2>
    <input type="number" id="deleteCityId" placeholder="ID na zmazanie">
    <button onclick="deleteCity()">Vymazať</button>

    <h2>Upraviť mesto</h2>
    <label>ID: <input type="number" id="updateCityId" placeholder="ID mesta"></label><br>
    <label>Nový názov: <input type="text" id="updateCityName" placeholder="Nový názov"></label><br>
    <button onclick="updateCity()">Upraviť</button>

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

    <pre id="output"></pre>

    <script>
        let apiPassword = '';

        // Function to set the password
        function setPassword() {
            const passwordInput = document.getElementById('apiPassword').value.trim();
            if (passwordInput) {
                apiPassword = passwordInput;
                document.getElementById('passwordStatus').textContent = '✅ Heslo bolo nastavené.';
            } else {
                document.getElementById('passwordStatus').textContent = '❌ Zadaj platné heslo.';
            }
        }

        // Helper function to include the password in headers
        function getHeaders() {
            const headers = { 'Content-Type': 'application/json' };
            if (apiPassword) {
                headers['password'] = apiPassword;
            }
            return headers;
        }

        function getSelectedEntity() {
            return document.getElementById("entityType").value;
        }

        function getEntityId() {
            return document.getElementById("entityId").value;
        }

        function loadEntityFromDropdown() {
            const entity = getSelectedEntity();
            const id = getEntityId();
            
            if(id.trim()) {
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
                headers: getHeaders(),
                body: JSON.stringify({ name })
            })
            .then(res => res.json().then(json => ({ status: res.status, json })))
            .then(({ status, json }) => {
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
                headers: getHeaders(),
                body: JSON.stringify({ name })
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
                headers: getHeaders(),
                body: JSON.stringify({ short_name, full_name, city_id })
            })
            .then(res => res.json().then(json => ({ status: res.status, json })))
            .then(({ status, json }) => {
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
                headers: getHeaders(),
                body: JSON.stringify({ short_name, full_name, city_id })
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
            fetch(`api/${entity}`, {
                headers: getHeaders()
            })
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
            fetch(`api/${entity}/${id}`, {
                headers: getHeaders()
            })
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
                method: 'DELETE',
                headers: getHeaders()
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
        const res = await fetch(`/api/${endpoint}`, {
            headers: getHeaders()
        });
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
            headers: getHeaders(),
            body: JSON.stringify({ player_id: playerId, roster_id: rosterId })
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
            headers: getHeaders(),
            body: JSON.stringify({ player_id: playerId, duel_id: duelId, goal_count: goalCount, own_goal_count: ownGoalCount })
        });

        document.getElementById("output").textContent = JSON.stringify(await res.json(), null, 2);
        }

        async function loadPlayerRosters() {
        const res = await fetch("/api/player_roster", {
            headers: getHeaders()
        });
        document.getElementById("output").textContent = JSON.stringify(await res.json(), null, 2);
        }

        async function loadGoals() {
        const res = await fetch("/api/goal", {
            headers: getHeaders()
        });
        document.getElementById("output").textContent = JSON.stringify(await res.json(), null, 2);
        }

        loadInitialDropdowns();
    </script>
</body>
</html>
