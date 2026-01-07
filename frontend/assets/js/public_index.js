const API = "../backend/api/";

function qs(id) { return document.getElementById(id); }

async function fetchJSON(url, options = {}) {
  const res = await fetch(url, {
    headers: { "Content-Type": "application/json" },
    credentials: "include",
    ...options
  });
  return res.json();
}

/* ============ CHARGEMENT DES CATÉGORIES ============ */
async function loadCategories() {
  const data = await fetchJSON(API + "categories_list.php");
  const container = qs("publicCategoryContainer");
  if (!container) return;
  container.innerHTML = '';

  (data.data || []).forEach(c => {
    const chip = document.createElement("div");
    chip.className = "selection-chip animate__animated animate__fadeIn";

    // Choose the best logo: item logo, or the LPA brand logo, or a generic placeholder
    const logo = (c.league_logo_url && c.league_logo_url.trim() !== '') ? c.league_logo_url : 'assets/img/logo_lpa.png';

    chip.innerHTML = `
      <div class="chip-logo-container">
        <img src="${logo}" class="chip-logo" onerror="this.src='assets/img/tirage.png'">
      </div>
      <span class="chip-text"><i class="fas fa-trophy" style="color:var(--accent); font-size:0.8rem; margin-right:5px;"></i> ${c.name} <small>(${c.season})</small></span>
    `;

    chip.onclick = () => {
      // Manage active state
      container.querySelectorAll(".selection-chip").forEach(el => el.classList.remove("active"));
      chip.classList.add("active");

      // Load relevant data
      loadDraws(c.id);
      loadSquads(c.id);
      loadScorers(c.id);
      loadDiscipline(c.id);
    };
    container.appendChild(chip);
  });
  const firstChip = container.querySelector(".selection-chip");
  if (firstChip) firstChip.click();
}

/* ============ CHARGEMENT DES TIRAGES ============ */
async function loadDraws(cat) {
  const data = await fetchJSON(API + "draw_list.php?category_id=" + cat);
  const container = qs("publicDrawContainer");
  if (!container) return;
  container.innerHTML = '';

  if (!data.data || data.data.length === 0) {
    container.innerHTML = '<p style="font-size:13px; color:var(--text-muted); font-style:italic;">Aucun tirage disponible.</p>';
    return;
  }

  (data.data || []).forEach(d => {
    const chip = document.createElement("div");
    chip.className = "selection-chip animate__animated animate__fadeIn";
    chip.innerHTML = `<i class="fas fa-star"></i> ${d.label}`;
    chip.onclick = () => {
      container.querySelectorAll(".selection-chip").forEach(el => el.classList.remove("active"));
      chip.classList.add("active");
      loadDrawDetails(d.id);
    };
    container.appendChild(chip);
  });
  const firstDrawChip = container.querySelector(".selection-chip");
  if (firstDrawChip) firstDrawChip.click();
}

/* ============ CHARGEMENT DES DÉTAILS DU TIRAGE ============ */
async function loadDrawDetails(drawId) {
  loadPoules(drawId);
  loadMatches(drawId);
  loadStandings(drawId);
  // loadScorers is now called at category level for better overview
}

/* ============ POULES ============ */
async function loadPoules(drawId) {
  const box = qs("publicPoules");
  box.innerHTML = "";

  const data = await fetchJSON(API + "draw_get.php?draw_id=" + drawId);

  data.data.poules.forEach(p => {
    const div = document.createElement("div");
    div.className = "poule-card";
    div.innerHTML = `<div class="poule-title">${p.name}</div>`;

    p.teams.forEach(t => {
      // Fallback logo si vide
      const logoUrl = t.logo_url && t.logo_url.trim() !== "" ? t.logo_url : "assets/img/default_logo.png";

      div.innerHTML += `
        <div class="team-row">
          <img src="${logoUrl}" class="team-avatar" onerror="this.src='assets/img/default_logo.png'">
          <span style="font-weight:600;">${t.name}</span>
        </div>
      `;
    });

    box.appendChild(div);
  });
}

/* ============ MATCHS ============ */
async function loadMatches(drawId) {
  const box = qs("publicMatches");
  box.innerHTML = "";

  const data = await fetchJSON(API + "match_list.php?draw_id=" + drawId);

  if (!data.success || data.data.length === 0) {
    box.innerHTML = "<p style='color:var(--text-muted); font-style:italic;'>Aucun match programmé pour le moment.</p>";
    return;
  }

  data.data.forEach(m => {
    const homeLogo = m.home_logo || "assets/img/default_logo.png";
    const awayLogo = m.away_logo || "assets/img/default_logo.png";

    box.innerHTML += `
      <div class="match-row">
        <!-- Équipe domicile -->
        <div style="display:flex; align-items:center; gap:10px; width:40%;">
          <img src="${homeLogo}" class="team-avatar" onerror="this.src='assets/img/default_logo.png'">
          <b style="font-size:16px;">${m.home_name}</b>
        </div>

        <!-- Score -->
        <div class="score-badge">
          ${m.score_home ?? "-"} : ${m.score_away ?? "-"}
        </div>

        <!-- Équipe extérieure -->
        <div style="display:flex; align-items:center; gap:10px; width:40%; justify-content:flex-end;">
          <b style="font-size:16px;">${m.away_name}</b>
          <img src="${awayLogo}" class="team-avatar" onerror="this.src='assets/img/default_logo.png'">
        </div>

      </div>
      <div style="text-align:center; font-size:13px; color:var(--text-muted); margin-bottom:20px;">
        📅 ${m.match_date ?? "Date non définie"} • 🕒 ${m.match_time ?? "--:--"} • 📍 ${m.location ?? "Terrain non défini"}
      </div>
    `;
  });
}

/* ============ CLASSEMENT ============ */
async function loadStandings(drawId) {
  const box = qs("publicStandings");
  box.innerHTML = "";

  const draw = await fetchJSON(API + "draw_get.php?draw_id=" + drawId);

  for (const p of draw.data.poules) {
    const st = await fetchJSON(API + "get_standings.php?poule_id=" + p.id);

    let html = `
      <div class="poule-card" style="margin-bottom:30px;">
      <div class="poule-title">Classement – ${p.name}</div>
      <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th style="text-align:left;">Équipe</th>
            <th>Pts</th>
            <th>J</th>
            <th>G</th>
            <th>N</th>
            <th>P</th>
            <th>Diff</th>
          </tr>
        </thead>
        <tbody>
    `;

    st.data.forEach((t, i) => {
      // Fallback logo si vide (au cas où on veut l'ajouter dans le tableau)
      // const logoUrl = t.logo_url || "../assets/img/default_logo.png";

      html += `
        <tr>
          <td style="font-weight:bold; color:var(--accent);">${i + 1}</td>
          <td style="text-align:left; display:flex; align-items:center; gap:10px;">
             ${t.name}
          </td>
          <td style="font-weight:bold; color:var(--accent); font-size:1.1em;">${t.points}</td>
          <td>${t.played}</td>
          <td>${t.wins}</td>
          <td>${t.draws}</td>
          <td>${t.losses}</td>
          <td>${t.goal_diff > 0 ? "+" + t.goal_diff : t.goal_diff}</td>
        </tr>
      `;
    });

    html += `</tbody></table></div></div>`;
    box.innerHTML += html;
  }
}

/* ============ STATS JOUEURS ============ */
let publicScorersChart = null;

async function loadScorers(catId = null) {
  const box = qs("publicScorers");
  const url = catId ? `${API}/player_stats_top.php?category_id=${catId}` : `${API}/player_stats_top.php`;
  const data = await fetchJSON(url);

  if (!data.success || !data.data || data.data.length === 0) {
    box.innerHTML = "<p style='color:var(--text-muted); text-align:center;'>Aucune statistique disponible.</p>";
    return;
  }

  const swimmers = data.data; // players
  const top3 = swimmers.slice(0, 3);
  const others = swimmers.slice(3, 10);

  // Layout structure
  box.innerHTML = `
    <div class="stats-layout">
      <div class="podium-container" id="statsPodium"></div>
      <div class="chart-container-public">
        <canvas id="publicScorersChart"></canvas>
      </div>
    </div>
    <div class="table-container" style="margin-top:40px;">
      <table id="statsFullTable">
        <thead>
          <tr>
            <th>#</th>
            <th style="text-align:left;">Joueur</th>
            <th style="text-align:left;">Équipe</th>
            <th>⚽ Buts</th>
            <th>🎯 Passes</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  `;

  // Render Podium
  const podiumBox = qs("statsPodium");
  const order = [1, 0, 2]; // 2nd, 1st, 3rd for visual alignment
  order.forEach(i => {
    const p = top3[i];
    if (!p) return;
    const card = document.createElement("div");
    card.className = `podium-card podium-${i + 1} animate__animated animate__fadeInUp`;
    card.style.animationDelay = `${i * 0.2}s`;

    const logo = p.logo_url || "assets/img/default_logo.png";
    card.innerHTML = `
      ${i === 0 ? '<i class="fas fa-crown crown"></i>' : ''}
      <img src="${logo}" class="podium-avatar" onerror="this.src='assets/img/default_logo.png'">
      <span class="podium-name">${p.player_name}</span>
      <span class="podium-team">${p.team_name}</span>
      <span class="podium-score">${p.total_goals}</span>
      <span style="font-size:10px; color:var(--text-muted);">BUTS</span>
    `;
    podiumBox.appendChild(card);
  });

  // Render Chart
  const labels = swimmers.slice(0, 8).map(p => p.player_name);
  const goals = swimmers.slice(0, 8).map(p => p.total_goals);
  renderPublicChart(labels, goals);

  // Render Table (Full)
  const tbody = qs("statsFullTable").querySelector("tbody");
  swimmers.forEach((p, i) => {
    const tr = document.createElement("tr");
    tr.className = "animate__animated animate__fadeIn";
    tr.style.animationDelay = `${i * 0.05}s`;
    tr.innerHTML = `
      <td style="font-weight:bold; color:var(--accent);">${i + 1}</td>
      <td style="font-weight:600; text-align:left;">${p.player_name}</td>
      <td style="text-align:left; display:flex; align-items:center; gap:8px;">
        <img src="${p.logo_url || 'assets/img/default_logo.png'}" style="width:24px; height:24px; border-radius:50%; object-fit:cover;">
        <span style="color:var(--text-muted);">${p.team_name}</span>
      </td>
      <td style="font-size:1.2em; font-weight:800; color:var(--accent);">${p.total_goals}</td>
      <td style="font-weight:600;">${p.total_assists}</td>
    `;
    tbody.appendChild(tr);
  });
}

function renderPublicChart(labels, values) {
  const ctx = qs("publicScorersChart").getContext("2d");
  if (publicScorersChart) publicScorersChart.destroy();

  publicScorersChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: 'Buts',
        data: values,
        backgroundColor: values.map((_, i) => i === 0 ? '#FFD700' : 'rgba(255, 215, 0, 0.4)'),
        borderColor: '#FFD700',
        borderWidth: 1,
        borderRadius: 8
      }]
    },
    options: {
      indexAxis: 'y', // Horizontal chart
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false }
      },
      scales: {
        x: {
          beginAtZero: true,
          grid: { color: 'rgba(255,255,255,0.05)' },
          ticks: { color: '#94A3B8' }
        },
        y: {
          grid: { display: false },
          ticks: { color: '#F8FAFC', font: { weight: '600' } }
        }
      }
    }
  });
}

/* ============ EFFECTIFS (SQUADS) ============ */
async function loadSquads(categoryId) {
  const box = qs("publicSquads");
  if (!box) return;
  box.innerHTML = "<p style='text-align:center; color:#FFD700;'>Chargement des effectifs...</p>";

  if (!categoryId) {
    box.innerHTML = "<p style='text-align:center; color:var(--text-muted);'>Sélectionnez une catégorie ci-dessus.</p>";
    return;
  }

  // 1. Get teams for this category
  const teamsRes = await fetchJSON(API + "teams_list.php?category_id=" + categoryId);

  if (!teamsRes.success || !teamsRes.data || teamsRes.data.length === 0) {
    box.innerHTML = "<p style='text-align:center; color:var(--text-muted);'>Aucune équipe dans cette catégorie.</p>";
    return;
  }

  box.innerHTML = "";

  // 2. For each team, we COULD fetch players individually, but that's many requests.
  // Better: loop and fetch. Or better: create a new endpoint? 
  // For now, let's just loop sequentially (it's public, caching applies).
  // Actually, let's display teams as expanding accordions or just list them.
  // Let's go with a Grid of Teams, clicking one shows players modal OR simple list.
  // Simple list is safer.

  const grid = document.createElement("div");
  grid.className = "filters-grid"; // Reusing grid style
  box.appendChild(grid);

  for (const team of teamsRes.data) {
    const teamCard = document.createElement("div");
    teamCard.style = `
      background: rgba(255,255,255,0.05); 
      border: 1px solid var(--glass-border); 
      border-radius: 12px; 
      padding: 15px;
    `;

    // Header
    const head = document.createElement("div");
    head.style = "display:flex; align-items:center; gap:10px; margin-bottom:10px; padding-bottom:10px; border-bottom:1px solid rgba(255,255,255,0.1);";
    const logo = team.logo_url || "assets/img/default_logo.png";
    head.innerHTML = `
      <img src="${logo}" style="width:40px; height:40px; border-radius:50%; object-fit:cover; border:2px solid var(--accent);">
      <h3 style="margin:0; color:var(--accent); font-size:18px;">${team.name}</h3>
    `;
    teamCard.appendChild(head);

    // Players List Container
    const pList = document.createElement("div");
    pList.innerHTML = "<i>Chargement...</i>";
    teamCard.appendChild(pList);

    grid.appendChild(teamCard);

    // Fetch players async
    fetchJSON(API + "players_list.php?team_id=" + team.id).then(pData => {
      pList.innerHTML = "";
      if (!pData.success || (pData.data || []).length === 0) {
        pList.innerHTML = "<span style='font-size:13px; color:var(--text-muted);'>Aucun joueur enregistré.</span>";
        return;
      }

      const players = pData.data;
      // Sort by Number then Name
      players.sort((a, b) => (a.number || 99) - (b.number || 99));

      players.forEach(p => {
        const row = document.createElement("div");
        row.style = "display:flex; justify-content:space-between; font-size:14px; margin-bottom:4px; padding:4px; border-radius:4px; background:rgba(0,0,0,0.2);";
        row.innerHTML = `
          <span><b style="color:#FFD700; display:inline-block; width:20px;">${p.number || '-'}</b> ${p.name}</span>
          <span style="color:var(--text-muted); font-size:12px;">${p.position || ''}</span>
        `;
        pList.appendChild(row);
      });
    });
  }
}

/* ============ DISCIPLINE (FAIR-PLAY) ============ */
async function loadDiscipline(catId = null) {
  const box = qs("publicDiscipline");
  if (!box) return;
  const url = catId ? `${API}discipline_list.php?category_id=${catId}` : `${API}discipline_list.php`;
  const data = await fetchJSON(url);

  if (!data.success || !data.data || data.data.length === 0) {
    box.innerHTML = "<p style='color:var(--text-muted); text-align:center;'>Aucune donnée de fair-play.</p>";
    return;
  }

  box.innerHTML = `
    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Équipe</th>
            <th>🟨 Jaunes</th>
            <th>🟥 Rouges</th>
            <th>Pts Malus</th>
          </tr>
        </thead>
        <tbody>
          ${data.data.map(d => `
            <tr>
              <td>${d.team_name}</td>
              <td>${d.yellow_cards}</td>
              <td>${d.red_cards}</td>
              <td style="color:var(--accent); font-weight:bold;">${d.discipline_points}</td>
            </tr>
          `).join('')}
        </tbody>
      </table>
    </div>
  `;
}

/* ============ PALMARES ============ */
async function loadPalmares() {
  const box = qs("publicPalmares");
  if (!box) return;
  const data = await fetchJSON(API + "palmares_list.php");

  if (!data.success || !data.data || data.data.length === 0) {
    box.innerHTML = "<p style='color:var(--text-muted)'>Le palmarès sera bientôt disponible.</p>";
    return;
  }

  box.innerHTML = data.data.map(p => `
    <div class="poule-card palmares-card">
      <span class="palmares-year">${p.season}</span>
      <div class="poule-title">${p.category_name}</div>
      <div style="font-weight:bold; color:#fff;">🏆 ${p.winner_name}</div>
    </div>
  `).join('');
}

/* ============ SPONSORS ============ */
async function loadSponsors() {
  const box = qs("publicSponsors");
  if (!box) return;
  const data = await fetchJSON(API + "sponsors_list.php");

  if (!data.success || !data.data || data.data.length === 0) {
    box.innerHTML = "<p style='color:var(--text-muted)'>Nos partenaires arrivent bientôt.</p>";
    return;
  }

  box.innerHTML = data.data.map(s => `
    <img src="${s.logo_url}" class="sponsor-logo" alt="${s.name}" title="${s.name}">
  `).join('');
}

/* ============ TERRAINS (LOCATIONS) ============ */
async function loadTerrains() {
  const box = qs("publicTerrains");
  if (!box) return;
  const data = await fetchJSON(API + "locations_list.php");

  if (!data.success || !data.data || data.data.length === 0) {
    box.innerHTML = "<p style='color:var(--text-muted)'>Aucun terrain répertorié.</p>";
    return;
  }

  box.innerHTML = data.data.map(l => `
    <div class="poule-card">
      <div class="poule-title">${l.name}</div>
      <p style="font-size:13px; color:var(--text-muted);"><i class="fas fa-map-pin"></i> ${l.address || 'Adresse non définie'}</p>
      <a href="reservation.html" class="selection-chip" style="text-decoration:none; display:inline-flex; margin-top:10px;">
        <i class="fas fa-calendar-alt"></i> Réserver
      </a>
    </div>
  `).join('');
}


/* === EVENTS === */
// Les listeners pour publicCategory et publicDraw ont été supprimés car remplacés par les chips
/* ============ FONCTIONNALITÉS SUPPLÉMENTAIRES (SEARCH & EXPORT) ============ */

/**
 * Filtre les éléments de la page en fonction de la recherche
 */
function initSearch() {
  const searchInput = qs("globalSearch");
  if (!searchInput) return;

  searchInput.addEventListener("input", (e) => {
    const term = e.target.value.toLowerCase().trim();

    // 1. Filtrer les Poules / Classements
    document.querySelectorAll(".poule-card, .team-row, tr, .match-row").forEach(el => {
      const text = el.innerText.toLowerCase();
      if (term === "" || text.includes(term)) {
        el.style.display = "";
      } else {
        // Ne pas cacher les lignes de header de tableau
        if (el.tagName === "TR" && el.querySelector("th")) return;
        el.style.display = "none";
      }
    });

    // 2. Gérer le message "Aucun résultat" si besoin (Optionnel)
  });
}

/**
 * Exporte une section en image PNG
 */
async function exportSection(event, containerId, fileName) {
  const element = document.getElementById(containerId);
  if (!element) return;

  const btn = event.currentTarget;
  const originalText = btn.innerHTML;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>...';

  try {
    const canvas = await html2canvas(element, {
      backgroundColor: "#0A2342", // Couleur de fond du projet
      scale: 2, // Pour une meilleure qualité
      logging: false,
      useCORS: true // Pour les images distantes si besoin
    });

    const link = document.createElement("a");
    link.download = `LiguePro_${fileName}_${new Date().toISOString().slice(0, 10)}.png`;
    link.href = canvas.toDataURL("image/png");
    link.click();
  } catch (err) {
    console.error("Erreur Export:", err);
    alert("Erreur lors de la génération de l'image.");
  } finally {
    btn.innerHTML = originalText;
  }
}

/* ============ BOUTIQUE PREVIEW ============ */
async function loadBoutiquePreview() {
  const box = qs("publicBoutiquePreview");
  if (!box) return;
  const data = await fetchJSON(API + "products_list.php");

  if (!data.success || !data.data || data.data.length === 0) {
    box.innerHTML = "<p style='color:var(--text-muted)'>Boutique en cours de mise à jour.</p>";
    return;
  }

  // Afficher seulement les 3 premiers produits
  box.innerHTML = data.data.slice(0, 3).map(p => `
    <div class="poule-card" style="text-align:center;">
      <img src="${p.image_url || 'https://via.placeholder.com/150'}" style="width:100px; height:100px; object-fit:contain; margin-bottom:10px;">
      <div class="poule-title" style="font-size:14px;">${p.name}</div>
      <div style="color:var(--accent); font-weight:bold; margin:5px 0;">${p.price} FCFA</div>
      <a href="boutique.html" class="selection-chip" style="text-decoration:none; font-size:11px;">Acheter</a>
    </div>
  `).join('');
}

/* ============ BLOG & CHAT ============ */

/**
 * Charge les articles du blog
 */
async function loadBlog() {
  const container = qs("publicBlog");
  if (!container) return;

  try {
    const resp = await fetch('../backend/api/blog_list.php');
    const res = await resp.json();

    if (!res.success || !res.data || res.data.length === 0) {
      container.innerHTML = "<p style='color:var(--text-muted)'>Aucune actualité pour le moment.</p>";
      return;
    }

    container.innerHTML = res.data.map(p => `
            <div class="blog-card animate__animated animate__fadeInUp">
                <img src="${p.image_url || 'assets/img/default_news.jpg'}" class="blog-img" alt="News">
                <div class="blog-content">
                    <span class="blog-date">${new Date(p.created_at).toLocaleDateString()}</span>
                    <h3 class="blog-title">${p.title}</h3>
                    <p style="font-size:13px; color:var(--text-muted);">${p.content.substring(0, 100)}...</p>
                </div>
            </div>
        `).join('');
  } catch (err) {
    console.error("Erreur Blog:", err);
  }
}

/**
 * Chat Logic
 */
function toggleChat() {
  const win = qs("chatWindow");
  win.style.display = (win.style.display === 'flex') ? 'none' : 'flex';
  if (win.style.display === 'flex') {
    loadChatMessages();
  }
}

async function loadChatMessages() {
  try {
    const resp = await fetch('../backend/api/chat_messages.php');
    const msgs = await resp.json();
    const container = qs("chatMessages");

    container.innerHTML = msgs.map(m => `
            <div class="chat-msg animate__animated animate__fadeIn">
                <span class="name">${m.nickname}</span>
                ${m.message}
            </div>
        `).join('');
    container.scrollTop = container.scrollHeight;
  } catch (err) { }
}

async function sendChatMessage() {
  const nick = qs("chatNickname").value || "Anonyme";
  const msg = qs("chatMsgInput").value;
  if (!msg) return;

  try {
    await fetch('../backend/api/chat_send.php', {
      method: 'POST',
      body: JSON.stringify({ nickname: nick, message: msg })
    });
    qs("chatMsgInput").value = "";
    loadChatMessages();
  } catch (err) { }
}

// Polling pour le chat (toutes les 5 secondes)
setInterval(() => {
  if (qs("chatWindow").style.display === 'flex') {
    loadChatMessages();
  }
}, 5000);

/* INIT */
document.addEventListener("DOMContentLoaded", () => {
  // 1. Chargement des données (indépendant)
  const loaders = [
    loadCategories,
    initSearch,
    loadBlog,
    loadScorers,
    loadDiscipline,
    loadPalmares,
    loadSponsors,
    loadTerrains,
    loadBoutiquePreview,
    loadMatchTicker
  ];

  loaders.forEach(fn => {
    try { fn(); } catch (e) { console.error("Loader error:", e); }
  });

  loadSquads(null);
});

async function loadMatchTicker() {
  const container = document.getElementById("matchTickerContainer");
  const ticker = document.getElementById("matchTicker");
  if (!container || !ticker) return;

  const today = new Date().toISOString().split('T')[0]; // YYYY-MM-DD

  try {
    const res = await fetch(`../backend/api/match_list.php?match_date=${today}`);
    const data = await res.json();

    if (!data.success || !data.data || data.data.length === 0) {
      container.style.display = "none";
      document.body.classList.remove("has-ticker");
      return;
    }

    container.style.display = "flex";
    document.body.classList.add("has-ticker");
    ticker.innerHTML = "";

    const matches = data.data;
    // Multiplier pour le scroll infini
    const displayMatches = [...matches, ...matches, ...matches];

    displayMatches.forEach(m => {
      const card = document.createElement("div");
      card.className = "match-card-ticker animate__animated animate__fadeIn";

      let statusClass = "status-planned";
      let statusLabel = m.match_time || "À venir";

      if (m.status === 'played') {
        statusClass = "status-played";
        statusLabel = "Terminé";
      } else if (m.status === 'live') {
        statusClass = "status-live";
        statusLabel = "DIRECT";
      }

      card.innerHTML = `
        <div class="ticker-team">
          <img src="${m.home_logo || 'assets/img/logo_lpa.png'}" alt="">
          <span>${m.home_name}</span>
        </div>
        <div class="ticker-score">${m.score_home ?? 0} - ${m.score_away ?? 0}</div>
        <div class="ticker-team">
          <span>${m.away_name}</span>
          <img src="${m.away_logo || 'assets/img/logo_lpa.png'}" alt="">
        </div>
        <span class="ticker-status ${statusClass}">${statusLabel}</span>
      `;
      ticker.appendChild(card);
    });

  } catch (err) {
    console.error("Ticker fetch error:", err);
    container.style.display = "none";
  }
}

// PARTNERSHIP FORM
async function submitPartnerRequest(e) {
  e.preventDefault();
  const status = document.getElementById("partnerStatus");
  const btn = e.target.querySelector('button');

  const payload = {
    name: document.getElementById("partner_name").value,
    league: document.getElementById("partner_league").value,
    phone: document.getElementById("partner_phone").value,
    message: document.getElementById("partner_message").value
  };

  btn.disabled = true;
  btn.textContent = "ENVOI EN COURS...";
  status.style.color = "var(--accent)";
  status.textContent = "Traitement de votre demande...";

  try {
    const res = await fetch('../backend/api/partner_apply.php', {
      method: 'POST',
      body: JSON.stringify(payload),
      headers: { 'Content-Type': 'application/json' }
    });

    if (!res.ok) throw new Error("Erreur réseau");
    const data = await res.json();

    if (data.success) {
      status.style.color = "#22c55e";
      status.innerHTML = `✅ ${data.message}`;
      e.target.reset();
      setTimeout(() => {
        closeModal('partnerContactModal');
        status.textContent = "";
      }, 4000);
    } else {
      throw new Error(data.message);
    }
  } catch (err) {
    status.style.color = "#ef4444";
    status.textContent = "Erreur: " + (err.message || "Impossible d'envoyer la demande.");
  } finally {
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-paper-plane"></i> Envoyer ma Demande';
  }
}
