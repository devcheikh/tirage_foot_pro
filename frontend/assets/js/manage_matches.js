// frontend/assets/js/manage_matches.js

const API_BASE = "../../backend/api/";

function qs(id) {
  return document.getElementById(id);
}

async function fetchJSON(url, options = {}) {
  const res = await fetch(url, {
    headers: { "Content-Type": "application/json" },
    credentials: "include",
    ...options,
  });
  return res.json();
}

let currentDrawData = null; // pour stocker poules + équipes

async function loadCategoriesMM() {
  const data = await fetchJSON(`${API_BASE}/categories_list.php`);
  const sel = qs("mmCategory");
  sel.innerHTML = '<option value="">-- Choisir --</option>';

  if (!data.success) {
    sel.innerHTML = '<option value="">Erreur</option>';
    return;
  }

  (data.data || []).forEach((c) => {
    const opt = document.createElement("option");
    opt.value = c.id;
    opt.textContent = `${c.name} (${c.season})`;
    sel.appendChild(opt);
  });
}

async function loadDrawsMM(categoryId) {
  const sel = qs("mmDraw");
  sel.innerHTML = "";

  if (!categoryId) {
    sel.innerHTML = '<option value="">Choisir une catégorie</option>';
    return;
  }

  const data = await fetchJSON(
    `${API_BASE}/draw_list.php?category_id=${categoryId}`
  );

  if (!data.success || (data.data || []).length === 0) {
    sel.innerHTML = '<option value="">Aucun tirage</option>';
    return;
  }

  sel.innerHTML = '<option value="">-- Choisir un tirage --</option>';
  (data.data || []).forEach((d) => {
    const opt = document.createElement("option");
    opt.value = d.id;
    opt.textContent = `${d.label} – ${d.category_name} (${d.season})`;
    sel.appendChild(opt);
  });
}

async function loadDrawDetailMM(drawId) {
  const pouleSel = qs("mmPoule");
  const teamHomeSel = qs("mmTeamHome");
  const teamAwaySel = qs("mmTeamAway");

  pouleSel.innerHTML = "";
  teamHomeSel.innerHTML = "";
  teamAwaySel.innerHTML = "";

  if (!drawId) {
    pouleSel.innerHTML = '<option value="">Choisir un tirage</option>';
    return;
  }

  const data = await fetchJSON(
    `${API_BASE}/draw_get.php?draw_id=${drawId}`
  );

  if (!data.success) {
    pouleSel.innerHTML = '<option value="">Erreur chargement</option>';
    return;
  }

  currentDrawData = data.data; // { draw, poules }

  const poules = currentDrawData.poules || [];
  pouleSel.innerHTML = '<option value="">-- Choisir une poule --</option>';
  poules.forEach((p) => {
    const opt = document.createElement("option");
    opt.value = p.id;
    opt.textContent = p.name;
    pouleSel.appendChild(opt);
  });
}

function updateTeamsForPoule(pouleId) {
  const teamHomeSel = qs("mmTeamHome");
  const teamAwaySel = qs("mmTeamAway");

  teamHomeSel.innerHTML = "";
  teamAwaySel.innerHTML = "";

  if (!currentDrawData || !pouleId) {
    teamHomeSel.innerHTML = '<option value="">Choisir une poule</option>';
    teamAwaySel.innerHTML = '<option value="">Choisir une poule</option>';
    return;
  }

  const poule = (currentDrawData.poules || []).find(
    (p) => String(p.id) === String(pouleId)
  );

  if (!poule || !poule.teams || poule.teams.length === 0) {
    teamHomeSel.innerHTML = '<option value="">Aucune équipe</option>';
    teamAwaySel.innerHTML = '<option value="">Aucune équipe</option>';
    return;
  }

  const optsDefault = '<option value="">-- Choisir une équipe --</option>';
  teamHomeSel.innerHTML = optsDefault;
  teamAwaySel.innerHTML = optsDefault;

  poule.teams.forEach((t) => {
    const opt1 = document.createElement("option");
    opt1.value = t.id;
    opt1.textContent = t.name;
    teamHomeSel.appendChild(opt1);

    const opt2 = document.createElement("option");
    opt2.value = t.id;
    opt2.textContent = t.name;
    teamAwaySel.appendChild(opt2);
  });
}

async function loadMatches(filters = {}) {
  const list = qs("mmList");
  list.innerHTML = "Chargement...";

  let url = `${API_BASE}/match_list.php`;
  const query = Object.keys(filters).map(k => `${k}=${filters[k]}`).join('&');
  if (query) url += '?' + query;

  const data = await fetchJSON(url);

  if (!data.success || (data.data || []).length === 0) {
    list.innerHTML = "<p>Aucun match programmé pour cette sélection.</p>";
    return;
  }

  list.innerHTML = "";
  (data.data || []).forEach((m) => {
    const div = document.createElement("div");
    div.className = "match-mini";

    const info = document.createElement("div");
    const pouleInfo = m.poule_name ? ` (Poule: ${m.poule_name})` : "";
    info.innerHTML = `<b>${m.home_name} vs ${m.away_name}</b><br>
                      <small>${m.match_date || ''} ${m.match_time || ''} - ${m.location || 'Lieu à définir'}${pouleInfo}</small>`;

    const actions = document.createElement("div");
    actions.className = "match-actions";

    const btnEdit = document.createElement("button");
    btnEdit.className = "btn-small btn-edit";
    btnEdit.textContent = "Modifier";
    btnEdit.onclick = () => editMatchMM(m);

    const btnDel = document.createElement("button");
    btnDel.className = "btn-small btn-delete";
    btnDel.textContent = "Supprimer";
    btnDel.onclick = () => deleteMatchMM(m.id, filters.poule_id);

    actions.appendChild(btnEdit);
    actions.appendChild(btnDel);

    div.appendChild(info);
    div.appendChild(actions);
    list.appendChild(div);
  });
}

function editMatchMM(m) {
  qs("mmId").value = m.id;
  qs("formTitle").textContent = "🏟 Modifier le match";
  qs("btnCreateMatch").textContent = "💾 Enregistrer les modifications";

  // Note: On pourrait recharger les catégories/tirages ici
  // mais on suppose qu'ils sont déjà disponibles ou non critiques
  // pour une modification rapide de l'heure/lieu.

  qs("mmTeamHome").value = m.team_home;
  qs("mmTeamAway").value = m.team_away;
  qs("mmDate").value = m.match_date || "";
  qs("mmTime").value = m.match_time || "";
  qs("mmLocation").value = m.location || "";

  window.scrollTo({ top: 0, behavior: 'smooth' });
}

async function deleteMatchMM(id, pouleId) {
  if (!confirm("Es-tu sûr de vouloir supprimer ce match ?")) return;

  const data = await fetchJSON(`${API_BASE}/match_delete.php`, {
    method: "POST",
    body: JSON.stringify({ id }),
  });

  if (data.success) {
    if (pouleId) loadMatches({ poule_id: pouleId });
    else loadMatches();
  } else {
    alert("Erreur: " + data.message);
  }
}

async function createMatchMM() {
  const matchId = parseInt(qs("mmId").value, 10) || 0;
  const categoryId = parseInt(qs("mmCategory").value, 10) || 0;
  const drawId = parseInt(qs("mmDraw").value, 10) || 0;
  const pouleId = parseInt(qs("mmPoule").value, 10) || 0;
  const teamHome = parseInt(qs("mmTeamHome").value, 10) || 0;
  const teamAway = parseInt(qs("mmTeamAway").value, 10) || 0;
  const matchDate = qs("mmDate").value;
  const matchTime = qs("mmTime").value;
  const location = qs("mmLocation").value.trim();
  const statusEl = qs("mmStatus");

  if (!categoryId || !drawId || !pouleId || !teamHome || !teamAway) {
    statusEl.style.color = "red";
    statusEl.textContent = "Tous les champs principaux sont obligatoires.";
    return;
  }

  const payload = {
    id: matchId,
    draw_id: drawId,
    poule_id: pouleId,
    team_home: teamHome,
    team_away: teamAway,
    match_date: matchDate || "",
    match_time: matchTime || "",
    location,
  };

  statusEl.style.color = "#FFD700";
  statusEl.textContent = matchId > 0 ? "Mise à jour du match..." : "Création du match...";

  const endpoint = matchId > 0 ? "match_update.php" : "match_create.php";
  const data = await fetchJSON(`${API_BASE}/${endpoint}`, {
    method: "POST",
    body: JSON.stringify(payload),
  });

  if (!data.success) {
    statusEl.style.color = "red";
    statusEl.textContent = "Erreur : " + (data.message || "inconnue");
    return;
  }

  statusEl.style.color = "#22c55e";
  statusEl.textContent = matchId > 0 ? "Match mis à jour avec succès." : "Match créé avec succès.";

  // Reset form
  qs("mmId").value = 0;
  qs("formTitle").textContent = "🏟 Nouveau match";
  qs("btnCreateMatch").textContent = "✅ Créer le match";
  qs("mmDate").value = "";
  qs("mmTime").value = "";
  qs("mmLocation").value = "";

  if (pouleId) loadMatches({ poule_id: pouleId });
  else loadMatches();
}

// Events
qs("mmCategory").addEventListener("change", () => {
  const catId = parseInt(qs("mmCategory").value, 10) || 0;
  currentDrawData = null;
  qs("mmDraw").innerHTML = "";
  qs("mmPoule").innerHTML = "";
  qs("mmTeamHome").innerHTML = "";
  qs("mmTeamAway").innerHTML = "";
  if (catId) loadDrawsMM(catId);
});

qs("mmDraw").addEventListener("change", () => {
  const drawId = parseInt(qs("mmDraw").value, 10) || 0;
  loadDrawDetailMM(drawId);
});

qs("mmPoule").addEventListener("change", () => {
  const pouleId = parseInt(qs("mmPoule").value, 10) || 0;
  updateTeamsForPoule(pouleId);
  if (pouleId) loadMatches({ poule_id: pouleId });
  else loadMatches();
});

qs("btnCreateMatch").addEventListener("click", createMatchMM);

// Init
loadCategoriesMM();
loadMatches(); // Load all matches initially
