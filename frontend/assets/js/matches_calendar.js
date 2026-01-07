// frontend/assets/js/matches_calendar.js

const API = "../../backend/api/";

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

let calDrawData = null;

async function checkSession() {
  try {
    const res = await fetch(`${API}/../auth/me.php`, { credentials: "include" });
    const data = await res.json();
    if (!data.success) {
      window.location.href = "login.html";
    }
  } catch (e) {
    // En cas d'erreur réseau, on présume déconnecté ou pb serveur
    console.error("Session check failed", e);
    // Optionnel : rediriger ou laisser l'utilisateur voir l'erreur
  }
}

// Vérifier la session au chargement
checkSession();

async function loadCategoriesCal() {
  const data = await fetchJSON(`${API}/categories_list.php`);
  const sel = qs("calCategory");
  sel.innerHTML = '<option value="">-- Choisir --</option>';
  if (!data.success) return;

  (data.data || []).forEach((c) => {
    const opt = document.createElement("option");
    opt.value = c.id;
    opt.textContent = `${c.name} (${c.season})`;
    sel.appendChild(opt);
  });
}

async function loadDrawsCal(categoryId) {
  const sel = qs("calDraw");
  sel.innerHTML = "";
  qs("calPoule").innerHTML = "";
  calDrawData = null;

  if (!categoryId) {
    sel.innerHTML = '<option value="">Choisir une catégorie</option>';
    return;
  }

  const data = await fetchJSON(`${API}/draw_list.php?category_id=${categoryId}`);
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

async function loadDrawDetailCal(drawId) {
  const pouleSel = qs("calPoule");
  pouleSel.innerHTML = "";

  if (!drawId) {
    pouleSel.innerHTML = '<option value="">Choisir un tirage</option>';
    return;
  }

  const data = await fetchJSON(`${API}/draw_get.php?draw_id=${drawId}`);
  if (!data.success) {
    pouleSel.innerHTML = '<option value="">Erreur chargement</option>';
    return;
  }

  calDrawData = data.data;
  const poules = calDrawData.poules || [];
  pouleSel.innerHTML = '<option value="">-- Choisir une poule --</option>';
  poules.forEach((p) => {
    const opt = document.createElement("option");
    opt.value = p.id;
    opt.textContent = p.name;
    pouleSel.appendChild(opt);
  });
}

async function loadMatchesCal(pouleId) {
  const cont = qs("calMatches");
  cont.innerHTML = "";

  if (!pouleId) {
    cont.innerHTML = "<p>Sélectionne une poule.</p>";
    qs("calStandings").innerHTML = "";
    return;
  }

  const data = await fetchJSON(`${API}/match_list.php?poule_id=${pouleId}`);
  if (!data.success || (data.data || []).length === 0) {
    cont.innerHTML = "<p>Aucun match.</p>";
    qs("calStandings").innerHTML = "";
    return;
  }

  (data.data || []).forEach((m) => {
    const row = document.createElement("div");
    row.className = "match-row";

    const teams = document.createElement("div");
    teams.className = "match-teams";
    teams.innerHTML = `
      <img class="match-logo" src="${m.home_logo || '../assets/img/default.png'}" alt="">
      <span>${m.home_name}</span>
      <strong>vs</strong>
      <span>${m.away_name}</span>
      <img class="match-logo" src="${m.away_logo || '../assets/img/default.png'}" alt="">
      <span style="font-size:12px;color:#C7D3E8;">
        ${m.match_date || ''} ${m.match_time || ''} – ${m.location || ''}
      </span>
    `;

    const controls = document.createElement("div");
    controls.className = "score-inputs";
    const sH = m.score_home ?? "";
    const sA = m.score_away ?? "";

    controls.innerHTML = `
      <input type="number" min="0" value="${sH}" id="scH_${m.id}">
      <span>:</span>
      <input type="number" min="0" value="${sA}" id="scA_${m.id}">
      <button class="btn-save-score" data-id="${m.id}">Enregistrer</button>
    `;

    row.appendChild(teams);
    row.appendChild(controls);
    cont.appendChild(row);
  });

  // attach events
  cont.querySelectorAll(".btn-save-score").forEach((btn) => {
    btn.addEventListener("click", async () => {
      try {
        const id = btn.getAttribute("data-id");
        const inpH = qs(`scH_${id}`);
        const inpA = qs(`scA_${id}`);

        if (!inpH || !inpA) {
          alert("Erreur: Champs de score introuvables.");
          return;
        }

        const sh = parseInt(inpH.value || "0", 10);
        const sa = parseInt(inpA.value || "0", 10);

        console.log(`Envoi score Match ${id}: ${sh}-${sa}`);

        const payload = {
          match_id: parseInt(id, 10),
          score_home: sh,
          score_away: sa,
        };

        const dataUp = await fetchJSON(`${API}/match_update_score.php`, {
          method: "POST",
          body: JSON.stringify(payload),
        });

        console.log("Réponse serveur:", dataUp);

        if (!dataUp.success) {
          alert("Erreur score : " + (dataUp.message || "inconnue"));
          return;
        }

        alert("Score enregistré avec succès !");
        await loadMatchesCal(pouleId);
        await loadStandingsCal(pouleId);

      } catch (err) {
        console.error("Erreur JS/Réseau:", err);
        alert("Une erreur est survenue : " + err.message);
      }
    });
  });

  await loadStandingsCal(pouleId);
}

async function loadStandingsCal(pouleId) {
  const cont = qs("calStandings");
  cont.innerHTML = "";

  const data = await fetchJSON(`${API}/get_standings.php?poule_id=${pouleId}`);
  if (!data.success || (data.data || []).length === 0) {
    cont.innerHTML = "<p>Aucun classement disponible.</p>";
    return;
  }

  const table = document.createElement("table");
  table.className = "standings";

  table.innerHTML = `
    <thead>
      <tr>
        <th>#</th>
        <th>Équipe</th>
        <th>J</th>
        <th>G</th>
        <th>N</th>
        <th>P</th>
        <th>BP</th>
        <th>BC</th>
        <th>Diff</th>
        <th>Pts</th>
      </tr>
    </thead>
    <tbody></tbody>
  `;

  const tbody = table.querySelector("tbody");
  data.data.forEach((st, idx) => {
    const tr = document.createElement("tr");
    tr.innerHTML = `
      <td>${idx + 1}</td>
      <td class="team-name-cell">
        <img class="std-logo" src="${st.logo_url || '../assets/img/default.png'}" alt="">
        <span>${st.name}</span>
      </td>
      <td>${st.played}</td>
      <td>${st.wins}</td>
      <td>${st.draws}</td>
      <td>${st.losses}</td>
      <td>${st.goals_for}</td>
      <td>${st.goals_against}</td>
      <td>${st.goal_diff}</td>
      <td>${st.points}</td>
    `;
    tbody.appendChild(tr);
  });

  cont.appendChild(table);
}

// EVENTS
qs("calCategory").addEventListener("change", () => {
  const catId = parseInt(qs("calCategory").value, 10) || 0;
  loadDrawsCal(catId);
  qs("calMatches").innerHTML = "";
  qs("calStandings").innerHTML = "";
});

qs("calDraw").addEventListener("change", () => {
  const drawId = parseInt(qs("calDraw").value, 10) || 0;
  loadDrawDetailCal(drawId);
  qs("calMatches").innerHTML = "";
  qs("calStandings").innerHTML = "";
});

qs("calPoule").addEventListener("change", () => {
  const pouleId = parseInt(qs("calPoule").value, 10) || 0;
  loadMatchesCal(pouleId);
});

// INIT
loadCategoriesCal();
