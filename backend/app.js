
const API = '/api';

async function fetchAndFill(url, selectId, labelFn) {
    const res = await fetch(`${API}${url}`);
    const data = await res.json();
    const select = document.getElementById(selectId);
    select.innerHTML = '';
    for (const item of data[Object.keys(data)[0]]) {
    const opt = document.createElement('option');
    opt.value = item.id;
    opt.textContent = labelFn(item);
    select.appendChild(opt);
    }
}

function refreshAllDropdowns() {
    fetchAndFill('/city/list', 'orgCitySelect', c => c.name);
    fetchAndFill('/city/list', 'tournamentCitySelect', c => c.name);
    fetchAndFill('/organization/list', 'rosterOrgSelect', o => o.name);
    fetchAndFill('/tournament/list', 'rosterTournamentSelect', t => `${t.name} (${t.year})`);
    fetchAndFill('/roster/list', 'rosterSelect', r => r.name);
    fetchAndFill('/player/list', 'playerSelect', p => `${p.player_first_name} ${p.player_last_name} (#${p.jersey_number})`);
}

async function createCity() {
    await fetch(`${API}/city/create`, {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({ name: document.getElementById('cityName').value })
    });
    refreshAllDropdowns();
}

async function createOrganization() {
    await fetch(`${API}/organization/create`, {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
        name: document.getElementById('orgName').value,
        city_id: document.getElementById('orgCitySelect').value
    })
    });

    refreshAllDropdowns();
}

async function createTournament() {
    await fetch(`${API}/tournament/create`, {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
        name: document.getElementById('tournamentName').value,
        year: document.getElementById('tournamentYear').value,
        city_id: document.getElementById('tournamentCitySelect').value,
        date: document.getElementById('tournamentDate').value
    })
    });
    refreshAllDropdowns();
}

async function createRoster() {
    await fetch(`${API}/roster/create`, {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
        name: document.getElementById('rosterName').value,
        tournament_id: document.getElementById('rosterTournamentSelect').value,
        organization_id: document.getElementById('rosterOrgSelect').value
    })
    });
    refreshAllDropdowns();
}

async function createPlayer() {
    await fetch(`${API}/player/create`, {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
        player_first_name: document.getElementById('playerFirstName').value,
        player_last_name: document.getElementById('playerLastName').value,
        jersey_number: document.getElementById('playerJersey').value
    })
    });
    refreshAllDropdowns();
}

async function addPlayerToRoster() {
    await fetch(`${API}/player_roster/add`, {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
        player_id: document.getElementById('playerSelect').value,
        roster_id: document.getElementById('rosterSelect').value
    })
    });
}

window.onload = refreshAllDropdowns;
