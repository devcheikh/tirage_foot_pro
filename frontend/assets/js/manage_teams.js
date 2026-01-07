console.log("JS LOADED v101");
// alert("DEBUG: JS Fichier chargé !"); // Décommentez si besoin

window.addEventListener('error', function (e) {
  alert("Erreur Script Global: " + e.message);
});

const API = "../../backend/api/";
const catSelectAdd = document.getElementById("teamCategory");
const catFilter = document.getElementById("filterCategory");
const teamsList = document.getElementById("teamsList");
const catStatus = document.getElementById("catStatus");
const teamStatus = document.getElementById("teamStatus");

async function fetchJSON(url, options = {}) {
  const res = await fetch(url, {
    headers: { "Content-Type": "application/json" },
    credentials: "include",
    ...options
  });
  return res.json();
}

function setStatus(el, msg, error = false) {
  el.textContent = msg;
  el.style.color = error ? "red" : "lime";
}

/* ------------------------
   CHARGER LES CATEGORIES
------------------------ */
async function loadCategories() {
  const data = await fetchJSON(API + "categories_list.php");

  if (!data.success) return;

  // Récupération fraîche des éléments
  const catSelectAdd = document.getElementById("teamCategory");
  const catFilter = document.getElementById("filterCategory");
  const listContainer = document.getElementById("categoriesList");

  console.log("Categories chargées:", data.data.length);

  catSelectAdd.innerHTML = '<option value="">-- Choisir une catégorie --</option>';
  catFilter.innerHTML = '<option value="">-- Filtrer par catégorie --</option>';

  if (listContainer) listContainer.innerHTML = "";

  data.data.forEach(cat => {
    // 1. Dropdowns
    const opt1 = document.createElement("option");
    opt1.value = cat.id;
    opt1.textContent = `${cat.name} (${cat.season})`;
    catSelectAdd.appendChild(opt1);

    const opt2 = opt1.cloneNode(true);
    catFilter.appendChild(opt2);

    // 2. Visible List
    if (listContainer) {
      const item = document.createElement("div");
      item.style.background = "#FFD70022";
      item.style.padding = "10px";
      item.style.borderRadius = "5px";
      item.style.borderLeft = "4px solid #FFD700";
      item.style.display = "flex";
      item.style.justifyContent = "space-between";
      item.style.alignItems = "center";

      const text = document.createElement("span");
      text.style.display = "flex";
      text.style.alignItems = "center";
      text.style.gap = "10px";

      const logoImg = document.createElement("img");
      logoImg.src = cat.league_logo_url || "../../assets/img/default-logo.png";
      logoImg.style.width = "30px";
      logoImg.style.height = "30px";
      logoImg.style.objectFit = "contain";
      logoImg.style.background = "#fff";
      logoImg.style.borderRadius = "3px";

      text.appendChild(logoImg);
      const span = document.createElement("span");
      span.textContent = `${cat.name} - ${cat.season}`;
      text.appendChild(span);

      // Bouton SUPPRIMER
      const btnDel = document.createElement("button");
      btnDel.textContent = "🗑️";
      btnDel.title = "Supprimer la catégorie";
      btnDel.style.margin = "0";
      btnDel.style.padding = "5px 10px";
      btnDel.style.background = "#B91C1C";
      btnDel.style.fontSize = "12px";
      btnDel.style.border = "none";
      btnDel.style.color = "white";
      btnDel.style.cursor = "pointer";
      btnDel.style.borderRadius = "5px";

      btnDel.onclick = () => deleteCategory(cat.id);

      // Bouton MODIFIER
      const btnEdit = document.createElement("button");
      btnEdit.textContent = "✏️";
      btnEdit.title = "Modifier la catégorie";
      btnEdit.style.margin = "0 5px 0 0";
      btnEdit.style.padding = "5px 10px";
      btnEdit.style.background = "#3b82f6";
      btnEdit.style.fontSize = "12px";
      btnEdit.style.border = "none";
      btnEdit.style.color = "white";
      btnEdit.style.cursor = "pointer";
      btnEdit.style.borderRadius = "5px";

      btnEdit.onclick = () => editCategory(cat);

      item.appendChild(text);
      item.appendChild(btnEdit);
      item.appendChild(btnDel);
      listContainer.appendChild(item);
    }
  });

  loadTeams(catFilter.value);
}

/* ------------------------
      MODIFIER CAT
------------------------ */
function editCategory(cat) {
  document.getElementById("catName").value = cat.name;
  document.getElementById("catSeason").value = cat.season;
  document.getElementById("catLogo").value = cat.league_logo_url || "";

  if (cat.league_logo_url) {
    const preview = document.getElementById("catLogoPreview");
    preview.src = cat.league_logo_url;
    preview.style.display = "inline-block";
    document.getElementById("catLogoStatus").textContent = "Logo actuel";
    document.getElementById("catLogoStatus").style.color = "lime";
  }

  // Store the ID for update
  document.getElementById("btnAddCat").setAttribute("data-edit-id", cat.id);
  document.getElementById("btnAddCat").textContent = "💾 Mettre à jour la catégorie";

  window.scrollTo({ top: 0, behavior: 'smooth' });
}

/* ------------------------
      SUPPRIMER CAT
------------------------ */
async function deleteCategory(id) {
  if (!confirm("Voulez-vous vraiment supprimer cette catégorie ?")) return;

  const data = await fetchJSON(API + "category_delete.php?id=" + id);
  if (data.success) {
    alert("Catégorie supprimée !");
    loadCategories();
  } else {
    alert(data.message);
  }
}

/* ------------------------
       AJOUT CATÉGORIE
------------------------ */
document.getElementById("btnAddCat").addEventListener("click", async () => {
  const name = document.getElementById("catName").value.trim();
  const season = document.getElementById("catSeason").value.trim();
  const league_logo_url = document.getElementById("catLogo").value.trim();
  const statusEl = document.getElementById("catStatus");
  const btn = document.getElementById("btnAddCat");
  const editId = btn.getAttribute("data-edit-id");

  console.log("Adding/Updating category:", name, season, editId);

  if (!name || !season) {
    statusEl.textContent = "Veuillez remplir tous les champs";
    statusEl.style.color = "red";
    return;
  }

  statusEl.textContent = editId ? "Mise à jour en cours..." : "Ajout en cours...";
  statusEl.style.color = "#FFD700";

  try {
    const endpoint = editId ? "category_update.php" : "category_add.php";
    const payload = { name, season, league_logo_url };
    if (editId) payload.id = parseInt(editId);

    const data = await fetchJSON(API + endpoint, {
      method: "POST",
      body: JSON.stringify(payload)
    });

    if (!data.success) {
      console.error("Erreur API Cat:", data);
      statusEl.textContent = data.message;
      statusEl.style.color = "red";
      return;
    }

    statusEl.textContent = editId ? "Catégorie mise à jour !" : "Catégorie ajoutée !";
    statusEl.style.color = "lime";

    // Clear inputs and reset button
    document.getElementById("catName").value = "";
    document.getElementById("catSeason").value = "";
    document.getElementById("catLogo").value = "";
    document.getElementById("catLogoFile").value = "";
    document.getElementById("catLogoPreview").style.display = "none";
    document.getElementById("catLogoStatus").textContent = "";
    btn.removeAttribute("data-edit-id");
    btn.textContent = "Ajouter la catégorie";

    loadCategories();
  } catch (err) {
    console.error("Erreur JS/Network Cat:", err);
    statusEl.textContent = "Erreur technique";
    statusEl.style.color = "red";
  }
});



// UPLOAD LOGO LIGUE AUTOMATIQUE
document.getElementById("catLogoFile").addEventListener("change", async function () {
  const fileInput = this;
  const logoStatus = document.getElementById("catLogoStatus");
  const logoPreview = document.getElementById("catLogoPreview");
  const hiddenInput = document.getElementById("catLogo");

  if (!fileInput.files.length) return;

  const file = fileInput.files[0];
  const formData = new FormData();
  formData.append("logo", file);

  logoStatus.textContent = "Upload en cours...";
  logoStatus.style.color = "#FFD700";

  try {
    const res = await fetch(API + "upload_logo.php", {
      method: "POST",
      body: formData,
      credentials: "include"
    });

    const data = await res.json();

    if (data.success) {
      hiddenInput.value = data.data.url;
      logoStatus.textContent = "Logo Ligue OK";
      logoStatus.style.color = "lime";
      logoPreview.src = data.data.url;
      logoPreview.style.display = "inline-block";
    } else {
      logoStatus.textContent = "Erreur: " + data.message;
      logoStatus.style.color = "red";
      fileInput.value = "";
      hiddenInput.value = "";
      logoPreview.style.display = "none";
    }
  } catch (err) {
    logoStatus.textContent = "Erreur réseau";
    logoStatus.style.color = "red";
  }
});


// UPLOAD LOGO AUTOMATIQUE
document.getElementById("teamLogoFile").addEventListener("change", async function () {
  const fileInput = this;
  const logoStatus = document.getElementById("logoStatus");
  const logoPreview = document.getElementById("logoPreview");
  const hiddenInput = document.getElementById("teamLogo");

  if (!fileInput.files.length) return;

  const file = fileInput.files[0];
  const formData = new FormData();
  formData.append("logo", file);

  // Feedback visuel immédiat
  logoStatus.textContent = "Upload en cours...";
  logoStatus.style.color = "#FFD700"; // Jaune

  try {
    const res = await fetch(API + "upload_logo.php", {
      method: "POST",
      body: formData,
      credentials: "include"
    });

    const data = await res.json();

    if (data.success) {
      // Succès
      hiddenInput.value = data.data.url;
      logoStatus.textContent = "Logo OK";
      logoStatus.style.color = "lime";

      // Afficher l'aperçu
      logoPreview.src = data.data.url;
      logoPreview.style.display = "inline-block";
    } else {
      // Erreur API
      logoStatus.textContent = "Erreur: " + data.message;
      logoStatus.style.color = "red";
      fileInput.value = ""; // Reset
      hiddenInput.value = "";
      logoPreview.style.display = "none";
    }
  } catch (err) {
    logoStatus.textContent = "Erreur réseau";
    logoStatus.style.color = "red";
    console.error(err);
  }
});

// Supprimer l'ancienne fonction uploadLogo qui n'était pas utilisée
// async function uploadLogo() { ... }

/* ------------------------
   BIBLIOTHÈQUE DE LOGOS
------------------------ */
const modalLib = document.getElementById("logoLibraryModal");
const gallery = document.getElementById("libGallery");

document.getElementById("btnOpenLib").addEventListener("click", async () => {
  modalLib.style.display = "flex";
  await loadLogoLibrary();
});

document.getElementById("closeLib").addEventListener("click", () => {
  modalLib.style.display = "none";
});

// Fermer si on clique dehors
modalLib.addEventListener("click", (e) => {
  if (e.target === modalLib) modalLib.style.display = "none";
});

async function loadLogoLibrary() {
  gallery.innerHTML = "<p style='color:white'>Chargement...</p>";

  const data = await fetchJSON(API + "get_logos.php");

  if (!data.success) {
    gallery.innerHTML = "<p style='color:red'>Erreur de chargement</p>";
    return;
  }

  gallery.innerHTML = "";

  if (data.data.length === 0) {
    gallery.innerHTML = "<p style='color:#ccc'>Aucun logo disponible.</p>";
    return;
  }

  data.data.forEach(item => {
    const img = document.createElement("img");
    img.src = item.url;
    img.title = item.filename;
    img.style.width = "100%";
    img.style.aspectRatio = "1/1";
    img.style.objectFit = "contain";
    img.style.border = "1px solid #FFD70055";
    img.style.borderRadius = "5px";
    img.style.cursor = "pointer";
    img.style.transition = "transform 0.2s";

    img.onmouseover = () => img.style.transform = "scale(1.1)";
    img.onmouseout = () => img.style.transform = "scale(1)";

    img.onclick = () => selectLogoFromLib(item.url);

    gallery.appendChild(img);
  });
}

function selectLogoFromLib(url) {
  document.getElementById("teamLogo").value = url;

  const preview = document.getElementById("logoPreview");
  preview.src = url;
  preview.style.display = "inline-block";

  const status = document.getElementById("logoStatus");
  status.textContent = "Logo sélectionné dans la bibliothèque";
  status.style.color = "lime";

  modalLib.style.display = "none";
  document.getElementById("teamLogoFile").value = ""; // Clear file input
}



/* ------------------------
        AJOUT EQUIPE
------------------------ */
document.getElementById("btnAddTeam").addEventListener("click", async () => {
  const btn = document.getElementById("btnAddTeam");
  const originalText = btn.textContent;

  // 1. Feedback immédiat
  btn.textContent = "⏳ Envoi...";
  btn.disabled = true;
  setStatus(teamStatus, "Traitement...", false);

  const payload = {
    category_id: document.getElementById("teamCategory").value,
    name: document.getElementById("teamName").value.trim(),
    city: document.getElementById("teamCity").value.trim(),
    logo_url: document.getElementById("teamLogo").value.trim(),
    is_seeded: document.getElementById("teamSeeded").value
  };

  console.log("Adding team...", payload);

  if (!payload.category_id) {
    btn.textContent = originalText;
    btn.disabled = false;
    alert("❌ Attention : Vous devez choisir une catégorie dans la liste !");
    return;
  }

  if (!payload.name) {
    btn.textContent = originalText;
    btn.disabled = false;
    alert("❌ Attention : Le nom de l'équipe est obligatoire !");
    return;
  }

  try {
    const data = await fetchJSON(API + "team_add.php", {
      method: "POST",
      body: JSON.stringify(payload)
    });

    if (!data.success) {
      console.error("Erreur API:", data);
      btn.textContent = originalText;
      btn.disabled = false;
      alert("❌ Erreur Serveur : " + data.message);
      return;
    }

    // Success
    alert("✅ Équipe ajoutée avec succès !");

    // Reset Form
    document.getElementById("teamName").value = "";
    document.getElementById("teamCity").value = "";
    document.getElementById("teamLogoFile").value = "";
    document.getElementById("logoPreview").style.display = "none";
    document.getElementById("teamLogo").value = "";
    document.getElementById("logoStatus").textContent = ""; /* Clear logo status too */

    loadTeams(catFilter.value);
  } catch (err) {
    console.error("Erreur JS/Network:", err);
    alert("❌ Erreur Technique : " + err.message);
  } finally {
    btn.textContent = originalText;
    btn.disabled = false;
    setStatus(teamStatus, "", false); // clear status
  }
});


/* ------------------------
    LISTE EQUIPES
------------------------ */
async function loadTeams(category_id) {
  if (!category_id) return;

  const data = await fetchJSON(API + "teams_list.php?category_id=" + category_id);

  if (!data.success) {
    teamsList.innerHTML = "<p>Erreur</p>";
    return;
  }

  teamsList.innerHTML = "";

  data.data.forEach(team => {
    const div = document.createElement("div");
    div.className = "teamRow";
    div.style.marginBottom = "8px";

    div.innerHTML = `
      <strong>${team.name}</strong> 
      ${team.is_seeded ? "⭐" : ""}
      <button onclick="deleteTeam(${team.id})">Supprimer</button>
    `;

    teamsList.appendChild(div);
  });
}

async function deleteTeam(id) {
  if (!confirm("Supprimer cette équipe ?")) return;

  const data = await fetchJSON(API + "team_delete.php?id=" + id);

  if (!data.success) {
    alert(data.message);
    return;
  }

  loadTeams(catFilter.value);
}

/* INIT */
catFilter.addEventListener("change", () => loadTeams(catFilter.value));
loadCategories();
