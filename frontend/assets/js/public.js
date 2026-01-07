// frontend/assets/js/public.js

const API_BASE = "../backend/api";

async function fetchJSON(url, options = {}) {
  const res = await fetch(url, {
    headers: { "Content-Type": "application/json" },
    credentials: "include",
    ...options,
  });
  return res.json();
}

const categoryFilter = document.getElementById("categoryFilter");
const drawSelect = document.getElementById("drawSelect");
const statusEl = document.getElementById("statusPublic");
const drawInfo = document.getElementById("drawInfo");
const poulesContainer = document.getElementById("poulesContainerPublic");

function setStatus(msg, isError = false) {
  statusEl.textContent = msg;
  statusEl.style.color = isError ? "red" : "#9ca3af";
}

/* -------------------------------------------
   🎨 RENDU DES POULES AVEC LOGOS
-------------------------------------------- */

function renderPoules(data) {
  const { draw, poules } = data;

  if (!draw) {
    poulesContainer.innerHTML = '<p class="empty">Tirage introuvable.</p>';
    drawInfo.textContent = "";
    return;
  }

  drawInfo.textContent =
    `${draw.label} – ${draw.category_name} (${draw.season}) – ` +
    `${draw.nb_poules} poule(s)`;

  if (!poules || poules.length === 0) {
    poulesContainer.innerHTML = '<p class="empty">Aucune poule enregistrée.</p>';
    return;
  }

  poulesContainer.innerHTML = "";

  poules.forEach((p) => {
    const card = document.createElement("div");
    card.className = "poule-card";

    /* --- HEADER DE LA POULE --- */
    const header = document.createElement("div");
    header.className = "poule-header";

    const title = document.createElement("div");
    title.className = "poule-title";
    title.textContent = p.name;

    const badge = document.createElement("div");
    badge.className = "poule-badge";
    badge.textContent = `${(p.teams || []).length} équipe(s)`;

    header.appendChild(title);
    header.appendChild(badge);
    card.appendChild(header);

    /* --- LISTE DES ÉQUIPES --- */
    const list = document.createElement("ol");
    list.className = "team-list";

    (p.teams || []).forEach((t) => {
      const li = document.createElement("li");
      li.className = "team-item";

      /* --- LOGO --- */
      const logo = document.createElement("img");
      logo.className = "team-logo";
      logo.src = t.logo_url ? t.logo_url : "../assets/img/default.png";
      logo.alt = t.name;

      /* --- NOM ÉQUIPE --- */
      const nameSpan = document.createElement("span");
      nameSpan.textContent = t.name + (t.is_seeded ? " ⭐" : "");

      li.appendChild(logo);
      li.appendChild(nameSpan);

      list.appendChild(li);
    });

    card.appendChild(list);
    poulesContainer.appendChild(card);
  });
}

/* -------------------------------------------
   📂 CHARGEMENT DES CATÉGORIES
-------------------------------------------- */

async function loadCategoriesPublic() {
  const data = await fetchJSON(`${API_BASE}/categories_list.php`);
  if (!data.success) {
    setStatus(data.message || "Erreur categories", true);
    return;
  }

  (data.data || []).forEach((c) => {
    const opt = document.createElement("option");
    opt.value = c.id;
    opt.textContent = `${c.name} (${c.season})`;
    categoryFilter.appendChild(opt);
  });
}

/* -------------------------------------------
   📂 CHARGEMENT DES TIRAGES
-------------------------------------------- */

async function loadDraws(categoryId = 0) {
  setStatus("Chargement des tirages...");

  let url = `${API_BASE}/draw_list.php`;
  if (categoryId > 0) url += `?category_id=${categoryId}`;

  const data = await fetchJSON(url);
  if (!data.success) {
    setStatus(data.message || "Erreur tirages", true);
    drawSelect.innerHTML = '<option value="">Aucun tirage disponible</option>';
    return;
  }

  drawSelect.innerHTML = '<option value="">Sélectionne un tirage...</option>';
  (data.data || []).forEach((d) => {
    const opt = document.createElement("option");
    opt.value = d.id;
    opt.textContent = `${d.label} – ${d.category_name} (${d.season})`;
    drawSelect.appendChild(opt);
  });

  setStatus(`${(data.data || []).length} tirage(s) trouvé(s).`);
}

/* -------------------------------------------
   🎯 CHARGER LES DÉTAILS D'UN TIRAGE
-------------------------------------------- */

async function loadDrawDetails(drawId) {
  if (!drawId) {
    poulesContainer.innerHTML = '<p class="empty">Aucun tirage sélectionné.</p>';
    drawInfo.textContent = "";
    return;
  }

  setStatus("Chargement du tirage...");

  const data = await fetchJSON(`${API_BASE}/draw_get.php?draw_id=${drawId}`);
  if (!data.success) {
    setStatus(data.message || "Erreur chargement tirage", true);
    return;
  }

  renderPoules(data.data);
  setStatus("Tirage chargé.");
}

/* -------------------------------------------
   EVENTS
-------------------------------------------- */

categoryFilter.addEventListener("change", () => {
  const catId = parseInt(categoryFilter.value, 10) || 0;
  loadDraws(catId);
});

drawSelect.addEventListener("change", () => {
  const drawId = parseInt(drawSelect.value, 10) || 0;
  loadDrawDetails(drawId);
});

/* -------------------------------------------
   INIT
-------------------------------------------- */

(async function initPublic() {
  await loadCategoriesPublic();
  await loadDraws(0);
})();
