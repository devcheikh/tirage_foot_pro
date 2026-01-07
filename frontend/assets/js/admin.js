const API_BASE = "../../backend/api";

async function fetchJSON(url, options = {}) {
  const res = await fetch(url, {
    headers: { "Content-Type": "application/json" },
    credentials: "include", // pour la session PHP
    ...options,
  });
  return res.json();
}

const categorySelect = document.getElementById("categorySelect");
const nbPoulesInput = document.getElementById("nbPoules");
const labelInput = document.getElementById("labelTirage");
const statusEl = document.getElementById("status");
const poulesContainer = document.getElementById("poulesContainer");
const btnTirage = document.getElementById("btnTirage");

function setStatus(msg, isError = false) {
  statusEl.textContent = msg;
  statusEl.style.color = isError ? "red" : "lime";
}

async function loadCategories() {
  const data = await fetchJSON(`${API_BASE}/categories_list.php`);
  if (!data.success) return setStatus(data.message, true);

  categorySelect.innerHTML = "";
  data.data.forEach((c) => {
    const opt = document.createElement("option");
    opt.value = c.id;
    opt.textContent = `${c.name} (${c.season})`;
    categorySelect.appendChild(opt);
  });
}

function renderPoules(poules) {
  poulesContainer.innerHTML = "";
  poules.forEach((p) => {
    const div = document.createElement("div");
    div.className = "poule-card";
    const title = document.createElement("h3");
    title.textContent = p.name;
    div.appendChild(title);

    const ul = document.createElement("ul");
    p.teams.forEach((t) => {
      const li = document.createElement("li");
      li.textContent = t.name + (t.is_seeded ? " ⭐" : "");
      ul.appendChild(li);
    });

    div.appendChild(ul);
    poulesContainer.appendChild(div);
  });
}

btnTirage.addEventListener("click", async () => {
  const category_id = parseInt(categorySelect.value, 10);
  const nb_poules = parseInt(nbPoulesInput.value, 10);
  const label = labelInput.value;

  const data = await fetchJSON(`${API_BASE}/draw_create.php`, {
    method: "POST",
    body: JSON.stringify({ category_id, nb_poules, label }),
  });

  if (!data.success) return setStatus(data.message, true);

  setStatus(data.message);
  renderPoules(data.data.poules);
});

// init
loadCategories();
