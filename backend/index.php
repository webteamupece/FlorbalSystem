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

    <!-- GETTERS AND DELETERS -->
    <section>
        <h2>Získať entitu podľa ID alebo všetky entity</h2>

        <label for="entityType">Typ entity:</label>
        <select id="entityType">
        <option value="" disabled selected>-- Vyber --</option>
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
        <button id="loadEntities" onclick="loadEntityFromDropdown()" disabled>Zobraziť entitu</button>
        <button id="deleteEntity" onclick="deleteEntityFromDropdown()" disabled>Vymazať entitu</button>
        <div class="divider" style="margin-bottom: 5em;"></div>
    </section>

    <!-- SETTERS AND ALTERERS -->
    <section>
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
        <button id="reloadDropdowns">🔄 Reload</button>

        <form id="dataForm" style="padding-top:1em"></form>
        <button id="submitBtn" style="display:none;" disabled>Odoslať</button>
    </section>

    <!-- REMOVING PLAYER FROM ROSTER -->
    <section>
        <div class="divider" style="margin-bottom: 3em;"></div>
        <h2>Odstránenie hráča z rosteru</h2>
        <label for="rosterSelect">Vyber roster:</label>
            <select id="rosterSelect">
                <option value="" disabled selected>--- Vyber roster ---</option>
            </select>
            <button id="reloadRosterBtn" style="margin-left: 10px;">🔄 Reload</button>
            <br><br>
            
            <label for="playerInRosterSelect">Vyber hráča:</label>
            <select id="playerInRosterSelect" disabled>
                <option value="" disabled selected>--- Najprv vyber roster ---</option>
            </select>
            <br><br>
            
            <button id="removePlayerFromRosterBtn" disabled>Odstrániť hráča z rosteru</button>

        <div class="divider" style="margin-bottom: 3em;"></div>
    </section>
    

    <!-- CLEARING OUTPUT -->
    <section>
        <h3>Premazanie výstupu:</h3>
        <button onclick="document.getElementById('output').textContent = '';">🧹 Vymazať výstup</button>
    </section>

    <h2>Výstup: </h2>
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

        document.getElementById("entityType").addEventListener("change",() => {
            if(getSelectedEntity()) {
                document.getElementById("loadEntities").disabled = false;
                document.getElementById("deleteEntity").disabled = false;
            } else {
                document.getElementById("loadEntities").disabled = true;
                document.getElementById("deleteEntity").disabled = true;
            }

            if(getSelectedEntity() === "goal" || getSelectedEntity() === "player_roster") {
                document.getElementById("entityId").disabled = true;
            } else {
                document.getElementById("entityId").disabled = false;
            }
        });

        // ------------------------------------------------------------------------------------

        // Funkcia na načítanie všetkých rosterov
        function loadAllRosters() {
            fetch('/api/roster')
                .then(response => response.json())
                .then(data => {
                    const rosterSelect = document.getElementById('rosterSelect');
                    rosterSelect.innerHTML = '<option value="" disabled selected>--- Vyber roster ---</option>';
                    
                    data.forEach(roster => {
                        const option = document.createElement('option');
                        option.value = roster.id;
                        option.textContent = `${roster.id} - ${roster.name}`;
                        rosterSelect.appendChild(option);
                    });
                })
                .catch(err => {
                    console.error('Chyba pri načítaní rosterov:', err);
                    document.getElementById('output').textContent = 'Chyba pri načítaní rosterov.';
                });
        }

        // Funkcia na načítanie hráčov v roster
        function loadPlayersInRoster(rosterId) {
            
            fetch(`/api/players_in_roster/${rosterId}`)
                .then(response => response.json())
                .then(data => {
                    const playerSelect = document.getElementById('playerInRosterSelect');
                    playerSelect.innerHTML = '<option value="" disabled selected>--- Vyber hráča ---</option>';
                    
                    if (data.length === 0) {
                        const option = document.createElement('option');
                        option.disabled = true;
                        option.textContent = 'Žiadni hráči v tomto rostere';
                        playerSelect.appendChild(option);
                        document.getElementById('removePlayerFromRosterBtn').disabled = true;
                        return;
                    }
                    
                    data.forEach(playerRoster => {
                        const option = document.createElement('option');
                        option.value = JSON.stringify({
                            player_id: playerRoster.player_id,
                            roster_id: rosterId
                        });
                        option.textContent = `${playerRoster.player_id} - ${playerRoster.first_name} ${playerRoster.last_name}`;
                        playerSelect.appendChild(option);
                    });
                })
                .catch(err => {
                    console.error('Chyba pri načítaní hráčov v rostere:', err);
                    document.getElementById('output').textContent = 'Chyba pri načítaní hráčov v rostere.';
                });
        }

        // Funkcia na odstránenie hráča z rosteru
        function removePlayerFromRoster() {
            const playerInRosterSelect = document.getElementById('playerInRosterSelect');
            const selectedValue = JSON.parse(playerInRosterSelect.value);
            
            if (!selectedValue || !selectedValue.player_id || !selectedValue.roster_id) {
                document.getElementById('output').textContent = 'Prosím, vyber platného hráča z rosteru.';
                return;
            }
            
            const { player_id, roster_id } = selectedValue;
            
            fetch(`/api/player_roster/${player_id}/${roster_id}`, {
                method: 'DELETE'
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('output').textContent = JSON.stringify(data, null, 2);
                
                // Aktualizovať zoznam hráčov po úspešnom odstránení
                loadPlayersInRoster(roster_id);
                
                // Zobraziť správu o úspešnej operácii
                if (!data.error) {
                    document.getElementById('output').textContent = `Hráč s ID ${player_id} bol úspešne odstránený z rosteru s ID ${roster_id}.`;
                }
            })
            .catch(err => {
                console.error('Chyba pri odstraňovaní hráča z rosteru:', err);
                document.getElementById('output').textContent = 'Chyba pri odstraňovaní hráča z rosteru.';
            });
        }

        // Načítať rostery do selectu
        loadAllRosters();
        
        // Pridať event listenery
        const deletePlayerFromRosterSelect = document.getElementById('rosterSelect');
        const playerInRosterSelect = document.getElementById('playerInRosterSelect');
        const removeButton = document.getElementById('removePlayerFromRosterBtn');
        const reloadButton = document.getElementById('reloadRosterBtn');
        
        // Pridaný event listener pre reload tlačidlo
        reloadButton.addEventListener('click', function() {
            loadAllRosters();
            playerInRosterSelect.disabled = true;
            playerInRosterSelect.innerHTML = '<option value="" disabled selected>--- Najprv vyber roster ---</option>';
            removeButton.disabled = true;
        });

        deletePlayerFromRosterSelect.addEventListener('change', function() {
            if (this.value) {
                loadPlayersInRoster(this.value);
                playerInRosterSelect.disabled = false;
            } else {
                playerInRosterSelect.disabled = true;
                playerInRosterSelect.innerHTML = '<option value="" disabled selected>--- Najprv vyber roster ---</option>';
                removeButton.disabled = true;
            }
        });
        
        playerInRosterSelect.addEventListener('change', function() {
            removeButton.disabled = !this.value;
        });
        
        removeButton.addEventListener('click', function() {
            removePlayerFromRoster();
        });

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
                        { value: 'round_of_sixteen', label: 'Osemfinále' },
                        { value: 'quarterfinals', label: 'Štvrťfinále' },
                        { value: 'semifinals', label: 'Semifinále' },
                        { value: 'third_place', label: 'Zápas o tretie miesto' },
                        { value: 'finals', label: 'Finále' },
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
                        { value: 'SCHEDULED', label: 'Naplánovaný' },
                        { value: 'ONGOING', label: 'Prebiehajúci' },
                        { value: 'FINISHED', label: 'Ukončený' }
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

        function createEditContainer(selected) {
            // Vytvor container pre ID dropdown a label
            const idContainer = document.createElement('div');
            idContainer.style.marginTop = '10px';
            idContainer.style.marginBottom = '15px';
            idContainer.className = 'id-edit-container';

            // Label pre ID
            const idLabel = document.createElement('label');
            idLabel.textContent = 'Vyber existujúcu entitu pre editáciu: ';
            idContainer.appendChild(idLabel);

            // Select pre ID namiesto input
            const idSelect = document.createElement('select');
            idSelect.id = 'edit-id-select';
            idSelect.style.marginRight = '10px';
            
            // Pridaj default option
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = 'Pre nový záznam vyber túto možnosť';
            defaultOption.selected = true;
            idSelect.appendChild(defaultOption);
            
            // Načítaj existujúce entity do selectu
            loadEntitiesForSelect(selected, idSelect);
            
            idContainer.appendChild(idSelect);
            
            // Event listener pre zmenu v select elemente
            idSelect.addEventListener('change', function() {
                const selectedId = this.value;
                if (selectedId) {
                    loadEntityForEdit(selected, selectedId);
                } else {
                    // Reset formulára pre nový záznam
                    resetFormForNewEntity(selected);
                }
            });

            // Vloženie containera pred formulár
            dataForm.parentNode.insertBefore(idContainer, dataForm);
            
            // Zakážeme select pre tabuľky s kompozitným kľúčom
            if(selected == 'goal' || selected == 'player_roster') {
                idSelect.disabled = true;
            } else {
                idSelect.disabled = false;
            }
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

        // Resetovanie formulára pre nový záznam
        function resetFormForNewEntity(selected) {
            // Reset formulára
            dataForm.reset();
            
            // Zmena stavu submit tlačidla
            submitBtn.textContent = 'Odoslať';
            submitBtn.dataset.mode = 'create';
            delete submitBtn.dataset.entityId;
            
            // Resetovanie selectov s možnosťou závislých hodnôt (napr. roster pre duel)
            const selectElements = dataForm.querySelectorAll('select');
            selectElements.forEach(select => {
                select.selectedIndex = 0;  // Vyber prvú možnosť (--- Vyber ---)
                select.dispatchEvent(new Event('change'));  // Vyvolaj zmenu pre aktualizáciu závislých polí
            });
            
            // Vyprázdnenie inputov
            const inputElements = dataForm.querySelectorAll('input');
            inputElements.forEach(input => {
                input.value = '';
            });
            
            // Informácia pre používateľa
            // document.getElementById('output').textContent = 'Formulár pripravený na vytvorenie nového záznamu.';
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
            // Resetovať tlačidlo submit
            submitBtn.textContent = 'Odoslať';
            submitBtn.dataset.mode = 'create';
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
            resetFormForNewEntity(selected); // Resetuj formulár pre nový záznam
            
            // Pridaj ID pole a tlačidlo "Načítať" pre editáciu ak nejde o tabuľku goal alebo player_roster
            if (selected) {
                //Vymaž predchádzajúci input pre ID ak existuje
                const previousIdInput = document.querySelector('.id-edit-container');
                if (previousIdInput) {
                    previousIdInput.remove();
                }
                createEditContainer(selected);
                // if(selected == 'goal' || selected == 'player_roster') {
                //     document.getElementById('edit-id-input').disabled = true;
                // } else {
                //     document.getElementById('edit-id-input').disabled = false;
                // }
                // IdInputAdded = true; // Nastav, že input bol pridaný
            }
            // document.getElementById('edit-id-input').value = ''; // Resetuj ID input

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
                                            const roster1ChangeHandler = () => {
                                                const selectedRoster1 = roster1Select.value;
                                                const filteredData = data.filter(roster => roster.id.toString() !== selectedRoster1);
                                                const previousValueOfOtherRoster = roster2Select.value;

                                                // Temporarily remove the event listener to prevent re-fetching                              
                                                roster2Select.removeEventListener('change', roster2ChangeHandler);
                                                populateSelect(roster2Select, filteredData);
                                                roster2Select.addEventListener('change', roster2ChangeHandler);

                                                // console.log("Roster2 dáta:",previousValueOfOtherRoster);
                                                roster2Select.value = previousValueOfOtherRoster;
                                                validateForm();
                                            };

                                            const roster2ChangeHandler = () => {
                                                const selectedRoster2 = roster2Select.value;
                                                const filteredData = data.filter(roster => roster.id.toString() !== selectedRoster2);
                                                const previousValueOfOtherRoster = roster1Select.value;

                                                // Temporarily remove the event listener to prevent re-fetching
                                                roster1Select.removeEventListener('change', roster1ChangeHandler);
                                                populateSelect(roster1Select, filteredData);
                                                roster1Select.addEventListener('change', roster1ChangeHandler);
                                                
                                                // console.log("roster1 dáta:",previousValueOfOtherRoster);
                                                roster1Select.value = previousValueOfOtherRoster;
                                                validateForm();
                                            };

                                            roster1Select.addEventListener('change', roster1ChangeHandler);
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
                            
                            if (firstSelectedField == field.name && otherField.type == 'select') {
                                // Ak je druhý input už vyplnený, tak fetchni možnosti pre druhý select na základe prvého inputu
                                
                                fetch(otherField.optionsEndpoint + `/${select.value}`)
                                    .then(res => res.json())
                                    .then(data => {
                                        
                                        // Vyčisti obsah druhého selectu a vlož doň nové možnosti
                                        const select = document.querySelector(`select[name="${otherFieldName}"]`);
                                        
                                        if(selected === 'goal') {
                                            isProgrammaticChange = true; // Nastav programovú zmenu
                                            let incrementButtons = document.getElementsByClassName("incrementButton");
                                            let decrementButtons = document.getElementsByClassName("decrementButton");
                                            // console.log(incrementButtons,decrementButtons);
                                                                              
                                            Array.from(document.getElementsByClassName("incrementButton")).forEach(button => button.disabled = true);
                                            Array.from(document.getElementsByClassName("decrementButton")).forEach(button => button.disabled = true);
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

                                        // console.log(playerId,duelId);
                                        
                                        if (playerId && duelId) {
                                            fetch(`/api/goal/${playerId}/${duelId}`)
                                                .then(res => res.json())
                                                .then(data => {
                                                    const goalCountInput = document.querySelector('input[name="goal_count"]');
                                                    const ownGoalCountInput = document.querySelector('input[name="own_goal_count"]');
                                                    Array.from(document.getElementsByClassName("incrementButton")).forEach(button => button.disabled = false);
                                                    if (!data.error) {
                                                        goalCountInput.value = data.goal_count || 0;
                                                        ownGoalCountInput.value = data.own_goal_count || 0;
                                                        
                                                        if(data.goal_count > 0 || data.own_goal_count > 0) {
                                                            Array.from(document.getElementsByClassName("decrementButton")).forEach(button => button.disabled = false);
                                                        }
                                                    } else {
                                                        goalCountInput.value = null;
                                                        ownGoalCountInput.value = null;
                                                        document.getElementById("output").textContent = 'Hráč ešte v tomto zápase neskóroval.';
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
                        const previousFieldName = formFields[selected][index - 1]?.name;
                        
                        incrementButton.id = previousFieldName + 'IncrementButton';
                        incrementButton.className = 'incrementButton';
                        incrementButton.type = 'button';
                        incrementButton.disabled = true;
                        incrementButton.textContent = '+';
                        incrementButton.style.marginLeft = '0.5em';
                        incrementButton.addEventListener('click', () => {
                            //Pridaj gol
                            //Ak hráč ešte neskoroval v tom zapase a input je null tak + button setne input na 1
                            
                            if (!document.querySelector(`input[name="${previousFieldName}"]`).value) {
                                document.querySelector(`input[name="${previousFieldName}"]`).value = 1;
                                document.getElementById(previousFieldName + 'DecrementButton').disabled = false;
                            } else {
                                document.querySelector(`input[name="${previousFieldName}"]`).value = parseInt(document.querySelector(`input[name="${previousFieldName}"]`).value) + 1;
                            }
                            
                            // Zavolaj funkciu na odoslanie formulára
                            handleSubmitForm();
                        });

                        const decrementButton = document.createElement('button');
                        
                        decrementButton.id = previousFieldName + 'DecrementButton';
                        decrementButton.className = 'decrementButton';
                        decrementButton.type = 'button';
                        decrementButton.disabled = true;
                        decrementButton.textContent = '-';
                        decrementButton.style.marginLeft = '0.5em';
                        decrementButton.addEventListener('click', () => {
                            //Uber gol
                            const previousFieldName = formFields[selected][index - 1]?.name;
                            if(parseInt(document.querySelector(`input[name="${previousFieldName}"]`).value) - 1 == 0) {
                                document.querySelector(`input[name="${previousFieldName}"]`).value = null;
                                document.getElementById(previousFieldName + 'DecrementButton').disabled = true;
                            } else {
                                document.querySelector(`input[name="${previousFieldName}"]`).value = parseInt(document.querySelector(`input[name="${previousFieldName}"]`).value) - 1;
                            }

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
                // Ak nie su vybraté aj id hráča a id zápasu tak zakáž buttony
                if (selected === 'goal') {
                    const playerId = document.querySelector(`select[name="player_id"]`).value;
                    const duelId = document.querySelector(`select[name="duel_id"]`).value;
                    
                    if (!playerId || !duelId) {
                        Array.from(document.getElementsByClassName("incrementButton")).forEach(button => button.disabled = true);
                        Array.from(document.getElementsByClassName("decrementButton")).forEach(button => button.disabled = true);
                    }
                }
                validateForm();
                document.querySelectorAll('#dataForm input, #dataForm select').forEach(field => {
                    field.addEventListener('input', validateForm);
                    field.addEventListener('change', validateForm);
                });
            }
        });

        // Upravená funkcia loadEntityForEdit
        function loadEntityForEdit(entityType, id) {
            if (!id) return;
            
            fetch(`/api/${entityType}/${id}`)
                .then(res => res.json())
                .then(data => {
                    if (!data.error) {
                        // Vyplň formulárové polia
                        fillFormWithEntityData(data);
                        
                        // Zmeň text tlačidla na "Aktualizovať"
                        submitBtn.textContent = 'Aktualizovať';
                        submitBtn.dataset.mode = 'edit';
                        submitBtn.dataset.entityId = id;
                        
                        // Informuj používateľa
                        document.getElementById('output').textContent = `Údaje pre ${entityType} s ID ${id} načítané. Zmeňte hodnoty a kliknite na Aktualizovať.`;
                    } else {
                        document.getElementById('output').textContent = `Entita s ID ${id} nebola nájdená alebo došlo k chybe: ${data.error || 'Neznáma chyba'}`;
                    }
                })
                .catch(err => {
                    console.error('Chyba pri načítaní entity:', err);
                    document.getElementById('output').textContent = 'Nastala chyba pri komunikácii so serverom.';
                });
        }

        // Funkcia na vyplnenie formulára údajmi
        function fillFormWithEntityData(data) {
        const selected = insertAlter.value;
        
        // Pre každé pole vo formulári
        Object.entries(data).forEach(([key, value]) => {
            // Ak nie je ID, pokračuj (ID je len pre editáciu)
            if (key === 'id') return;
            
            // Nájdi zodpovedajúci input/select
            const field = document.querySelector(`[name="${key}"]`);
            
            if (field) {
                // Nastav hodnotu
                field.value = value;
                
                // Ak ide o tournament_id v duel, simuluj jeho zmenu pre vytvorenie roster selectov
                if (selected === 'duel' && key === 'tournament_id') {
                    field.value = value;
                    
                    // Keď máme turnaj, postarajme sa o rostery
                    setTimeout(() => {
                        ensureRosterSelectsForDuelEdit(value, data.roster1_id, data.roster2_id);
                    }, 300);
                    
                    // Stále vyvolajme event change pre iné závislosti
                    field.dispatchEvent(new Event('change'));
                }
                // Pre ostatné selecty vyvolaj event change normálne
                else if (field.tagName === 'SELECT') {
                    field.dispatchEvent(new Event('change'));
                }
            }
        });
        
        // Aktivuj tlačidlo odoslania
        validateForm();
    }

    // Pridajte túto funkciu na pomoc pri riešení edge cases s rostermi
    function ensureRosterSelectsForDuelEdit(tournamentId, roster1Id, roster2Id) {
        if (!tournamentId) return;
        
        const rosterFields = formFields.duel.find(field => field.name === 'tournament_id').rosters;
        const roster1Field = rosterFields.find(roster => roster.name === 'roster1_id');
        const roster2Field = rosterFields.find(roster => roster.name === 'roster2_id');

        let roster1Select = document.querySelector(`select[name="roster1_id"]`);
        let roster2Select = document.querySelector(`select[name="roster2_id"]`);

        // Vytvor selecty ak neexistujú
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
        
        // Načítaj rostery a nastav hodnoty
        fetch(`/api/available_rosters_for_tournament/${tournamentId}`)
            .then(res => res.json())
            .then(data => {
                // Načítaj údaje z API
                if (roster1Id && roster2Id) {
                    // Pre istotu načítame aj konkrétne rostery, ktoré potrebujeme
                    Promise.all([
                        fetch(`/api/roster/${roster1Id}`).then(res => res.json()),
                        fetch(`/api/roster/${roster2Id}`).then(res => res.json())
                    ]).then(([roster1, roster2]) => {
                        // Zabezpečíme, že rostery existujú v options
                        populateSelect(roster1Select, [...data, roster1]);
                        populateSelect(roster2Select, [...data, roster2]);
                        
                        // Nastavíme hodnoty
                        roster1Select.value = roster1Id;
                        roster2Select.value = roster2Id;
                        
                        // Vyvoláme udalosti pre validáciu
                        roster1Select.dispatchEvent(new Event('change'));
                        roster2Select.dispatchEvent(new Event('change'));
                    });
                } else {
                    // Štandardné naplnenie bez nastavenia hodnôt
                    populateSelect(roster1Select, data);
                    populateSelect(roster2Select, data);
                }
                
                // Pridanie event listenerov pre vzájomnú exklúziu
                // (event listenery z existujúceho kódu)
            })
            .catch(err => console.error('Chyba pri fetchnutí zostáv:', err));
    }

        // Funkcia na kontrolu, či sú všetky povinné polia vyplnené
        function validateForm() {
            const formFields = document.querySelectorAll('#dataForm input, #dataForm select');
            let isValid = true;

            formFields.forEach(field => {
                if (!field.value) {
                    isValid = false;
                }
            });

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

            //Ak je hodnota golov "" tak nastav na 0
            if (jsonData.goal_count === "") jsonData.goal_count = '0';
            if (jsonData.own_goal_count === "") jsonData.own_goal_count = '0';
            
            const selected = insertAlter.value;
            const outputElement = document.getElementById('output'); // Pridaná definícia

            // Zisti, či ide o editáciu alebo nový záznam
            const isEditMode = submitBtn.dataset.mode === 'edit';
            const entityId = submitBtn.dataset.entityId;

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
                        outputElement.textContent = 'Údaje sú už aktuálne, nie je potrebná žiadna aktualizácia.';
                    return;
                    }
                    //Ak hodnoty ktoré sa pokúsim nastaviť sú 0,0 tak vymaž údaj z databázy.
                    if (jsonData.goal_count === '0' && jsonData.own_goal_count === '0') {
                        const deleteResponse = await fetch(`/api/goal/${playerId}/${duelId}`, {
                            method: 'DELETE'
                        });

                        if (deleteResponse.ok) {
                            outputElement.textContent = 'Údaje boli úspešne vymazané.';
                        } else {
                            outputElement.textContent = 'Nastala chyba pri mazaní.';
                        }
                        return;
                    }  else  {
                        // Ak existuje a údaje sa líšia, použijeme PUT na aktualizáciu
                        const updateResponse = await fetch(`/api/goal/${playerId}/${duelId}`, {
                        method: 'PUT',
                        headers: getHeaders(),
                        body: JSON.stringify(jsonData)
                        });

                        if (updateResponse.ok) {
                            outputElement.textContent = JSON.stringify(jsonData, null, 2);
                        } else {
                            outputElement.textContent = 'Nastala chyba pri aktualizácii.';
                        }
                    }
                } else {
                    // Ak neexistuje, použijeme POST na vytvorenie
                    const createResponse = await fetch(`/api/goal`, {
                    method: 'POST',
                    headers: getHeaders(),
                    
                    body: JSON.stringify(jsonData)
                    });

                    if (createResponse.ok) {
                        outputElement.textContent = JSON.stringify(jsonData, null, 2);
                    } else {
                        outputElement.textContent = 'Nastala chyba pri ukladaní.';
                    }
                }
                } catch (err) {
                console.error('Chyba pri overovaní alebo ukladaní údajov:', err);
                outputElement.textContent = 'Nastala chyba pri komunikácii so serverom.';
                }
            } else {
                outputElement.textContent = 'Prosím, vyplňte všetky povinné polia.';
            }
            } else {
                // Pre ostatné tabuľky
                let url = `/api/${selected}`;
                let method = 'POST';
                
                // Ak je režim editácie, použijeme PUT a pridáme ID do URL
                if (isEditMode) {
                    url += `/${entityId}`;
                    method = 'PUT';
                }
                
                try {
                    const response = await fetch(url, {
                        method: method,
                        headers: getHeaders(),
                        body: JSON.stringify(jsonData)
                    });

                    if (response.ok) {
                        const responseData = await response.json();
                        
                        // Ak to bola úspešná editácia, aktualizuj stav
                        if (isEditMode) {
                            outputElement.textContent = `Údaje pre ${selected} s ID ${entityId} boli úspešne aktualizované:\n` + 
                                                    JSON.stringify(responseData, null, 2);
                        } else {
                            outputElement.textContent = JSON.stringify(responseData, null, 2);
                        }
                        
                        // Resetuj režim na CREATE po úspešnej operácii
                        if (isEditMode) {
                            submitBtn.textContent = 'Odoslať';
                            submitBtn.dataset.mode = 'create';
                            delete submitBtn.dataset.entityId;
                            resetFormForNewEntity(selected);
                        }
                    } else {
                        const errorData = await response.json();
                        outputElement.textContent = `Nastala chyba pri ${isEditMode ? 'aktualizácii' : 'ukladaní'}: ` + 
                                                JSON.stringify(errorData, null, 2);
                    }
                } catch (err) {
                    console.error(`Chyba pri ${isEditMode ? 'aktualizácii' : 'vytváraní'} záznamu:`, err);
                    outputElement.textContent = 'Nastala chyba pri komunikácii so serverom.';
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