const API = "../../backend/api/";

// Helper pour les sélecteurs
function qs(id) {
    return document.getElementById(id);
}

// Helper pour fetch
async function fetchJSON(url, options = {}) {
    const res = await fetch(url, {
        headers: { "Content-Type": "application/json" },
        credentials: "include",
        ...options
    });
    return res.json();
}

let allTeams = [];

// Charger la liste des équipes
async function loadTeamsPlayers() {
    const data = await fetchJSON(API + "/teams_list.php");
    if (!data.success) return;
    allTeams = data.data || [];

    const selAdd = qs("plTeamSelectAdd");
    const selFil = qs("plTeamFilter");
    const selImp = qs("plTeamSelectImport");

    if (selAdd) selAdd.innerHTML = '<option value="">-- Choisir une équipe --</option>';
    if (selImp) selImp.innerHTML = '<option value="">-- Choisir une équipe --</option>';

    allTeams.forEach(t => {
        const opt1 = document.createElement("option");
        opt1.value = t.id;
        opt1.textContent = t.name;
        if (selAdd) selAdd.appendChild(opt1);

        const opt2 = document.createElement("option");
        opt2.value = t.id;
        opt2.textContent = t.name;
        if (selFil) selFil.appendChild(opt2);

        const opt3 = document.createElement("option");
        opt3.value = t.id;
        opt3.textContent = t.name;
        if (selImp) selImp.appendChild(opt3);
    });
}

// Charger les joueurs d'une équipe
async function loadPlayersForTeam(teamId) {
    const list = qs("plList");
    if (!list) return;

    list.innerHTML = `<p style="color:#FFD700; text-align:center;">Chargement des joueurs...</p>`;

    if (!teamId) {
        list.innerHTML = "<p style='color:#94A3B8; text-align:center;'>Sélectionnez une équipe pour voir ses joueurs.</p>";
        return;
    }

    const data = await fetchJSON(API + "/players_list.php?team_id=" + teamId);
    if (!data.success || (data.data || []).length === 0) {
        list.innerHTML = "<p style='color:#94A3B8; text-align:center;'>Aucun joueur enregistré pour cette équipe.</p>";
        return;
    }

    list.innerHTML = "";// Clear loading or empty msg

    (data.data || []).forEach(p => {
        const row = document.createElement("div");
        row.className = "player-item animate__animated animate__fadeIn";
        row.style = `
      background: rgba(255, 255, 255, 0.03); 
      border: 1px solid rgba(255, 215, 0, 0.2); 
      border-radius: 12px; 
      padding: 15px; 
      display: flex; 
      align-items: center; 
      justify-content: space-between; 
      margin-bottom: 10px;
    `;

        const avatar = p.photo_url || '../assets/img/default-player.png'; // Fallback if needed

        // Left side: Avatar + Info
        const infoContainer = document.createElement("div");
        infoContainer.style.display = "flex";
        infoContainer.style.alignItems = "center";
        infoContainer.style.gap = "15px";

        const badge = document.createElement("div");
        badge.textContent = p.number || "#";
        badge.style = `
      background: #FFD700; 
      color: #001B48; 
      font-weight: bold; 
      width: 30px; 
      height: 30px; 
      display: flex; 
      align-items: center; 
      justify-content: center; 
      border-radius: 50%;
      font-size: 14px;
    `;

        const details = document.createElement("div");
        details.innerHTML = `
      <div style="font-size: 16px; font-weight: bold; color:white;">${p.name}</div>
      <div style="font-size: 13px; color: #94A3B8;">${p.position || 'Poste non défini'} • ${p.birthdate || '??/??/????'}</div>
    `;

        infoContainer.appendChild(badge);
        infoContainer.appendChild(details);

        // Right side: Actions
        const actionsContainer = document.createElement("div");
        actionsContainer.style.display = "flex";
        actionsContainer.style.gap = "8px";

        const btnEdit = document.createElement("button");
        btnEdit.innerHTML = "✏️";
        btnEdit.title = "Modifier";
        btnEdit.style = `
            background: rgba(59, 130, 246, 0.2); 
            color: #60A5FA; 
            border: 1px solid rgba(59, 130, 246, 0.5); 
            padding: 8px 12px; 
            border-radius: 8px; 
            cursor: pointer; 
            transition: all 0.2s;
        `;
        btnEdit.onclick = () => editPlayer(p);

        const btnDel = document.createElement("button");
        btnDel.innerHTML = "🗑️";
        btnDel.title = "Supprimer";
        btnDel.style = `
            background: rgba(185, 28, 28, 0.2); 
            color: #F87171; 
            border: 1px solid rgba(185, 28, 28, 0.5); 
            padding: 8px 12px; 
            border-radius: 8px; 
            cursor: pointer; 
            transition: all 0.2s;
        `;
        btnDel.onmouseover = () => btnDel.style.background = "rgba(185, 28, 28, 0.5)";
        btnDel.onmouseout = () => btnDel.style.background = "rgba(185, 28, 28, 0.2)";
        btnDel.onclick = async () => {
            if (!confirm("Voulez-vous vraiment supprimer " + p.name + " ?")) return;
            const delRes = await fetchJSON(API + "/players_delete.php?id=" + p.id);
            if (!delRes.success) {
                alert("Erreur suppression : " + (delRes.message || ''));
                return;
            }
            loadPlayersForTeam(teamId);
        };

        actionsContainer.appendChild(btnEdit);
        actionsContainer.appendChild(btnDel);

        row.appendChild(infoContainer);
        row.appendChild(actionsContainer);
        list.appendChild(row);
    });
}

function editPlayer(p) {
    qs("plId").value = p.id;
    qs("formTitle").textContent = "✏️ Modifier le joueur";
    qs("btnPlAdd").textContent = "💾 Enregistrer les modifications";

    qs("plTeamSelectAdd").value = p.team_id;
    qs("plName").value = p.name;
    qs("plPosition").value = p.position || "";
    qs("plNumber").value = p.number || "";
    qs("plLicense").value = p.license_number || "";
    qs("plBirth").value = p.birthdate || "";
    qs("plPhoto").value = p.photo_url || "";

    if (p.photo_url) {
        qs("plPhotoPreview").src = p.photo_url;
        qs("plPhotoPreview").style.display = "block";
    } else {
        qs("plPhotoPreview").style.display = "none";
    }

    // CV Fields
    qs("plVisible").checked = parseInt(p.is_visible) === 1;
    qs("plHeight").value = p.height || "";
    qs("plWeight").value = p.weight || "";
    qs("plFoot").value = p.preferred_foot || "";
    qs("plBio").value = p.bio || "";
    qs("plSkills").value = p.skills || "";
    qs("plVideo").value = p.video_url || "";

    // Stats
    qs("plMatches").value = p.matches_played || "";
    qs("plGoals").value = p.goals || "";
    qs("plAssists").value = p.assists || "";

    // Socials
    qs("plInsta").value = p.instagram || "";
    qs("plTwitter").value = p.twitter || "";
    qs("plLinkedin").value = p.linkedin || "";
    qs("plMotto").value = p.motto || "";

    window.scrollTo({ top: 0, behavior: 'smooth' });
}
async function addPlayer() {
    const teamId = parseInt(qs("plTeamSelectAdd").value, 10) || 0;
    const name = qs("plName").value.trim();
    const position = qs("plPosition").value.trim();
    const number = parseInt(qs("plNumber").value, 10) || 0;
    const birthdate = qs("plBirth").value;
    const photo_url = qs("plPhoto").value.trim();
    const status = qs("plStatus");

    const btnAdd = qs("btnPlAdd");
    const originalText = btnAdd.textContent;

    if (!teamId || !name) {
        status.style.color = "red";
        status.textContent = "❌ Équipe et nom sont obligatoires.";
        // Shake effect
        return;
    }

    // Loading state
    status.style.color = "#FFD700";
    status.textContent = "⏳ Ajout en cours...";
    btnAdd.textContent = "⏳ Envoi...";
    btnAdd.disabled = true;

    try {
        const payload = {
            id: parseInt(qs("plId").value, 10) || 0,
            team_id: teamId,
            name,
            position,
            number,
            license_number: qs("plLicense").value.trim(),
            birthdate,
            photo_url,
            is_visible: qs("plVisible").checked ? 1 : 0,
            height: parseFloat(qs("plHeight").value) || 0,
            weight: parseFloat(qs("plWeight").value) || 0,
            preferred_foot: qs("plFoot").value,
            bio: qs("plBio").value.trim(),
            skills: qs("plSkills").value.trim(),
            video_url: qs("plVideo").value.trim(),
            // New fields
            matches_played: parseInt(qs("plMatches").value) || 0,
            goals: parseInt(qs("plGoals").value) || 0,
            assists: parseInt(qs("plAssists").value) || 0,
            instagram: qs("plInsta").value.trim(),
            twitter: qs("plTwitter").value.trim(),
            linkedin: qs("plLinkedin").value.trim(),
            motto: qs("plMotto").value.trim()
        };

        const data = await fetchJSON(API + "/player_save.php", {
            method: "POST",
            body: JSON.stringify(payload)
        });

        if (!data.success) {
            status.style.color = "red";
            status.textContent = "❌ Erreur : " + (data.message || "inconnue");
            alert("Erreur serveur: " + data.message);
            return;
        }

        status.style.color = "#22c55e";
        status.textContent = "✅ Joueur ajouté avec succès.";

        // Reset fields
        qs("plId").value = "0";
        qs("formTitle").textContent = "➕ Ajouter un joueur";
        qs("btnPlAdd").textContent = "Ajouter le joueur";

        qs("plName").value = "";
        qs("plPosition").value = "";
        qs("plNumber").value = "";
        qs("plLicense").value = "";
        qs("plBirth").value = "";
        qs("plPhoto").value = "";
        qs("plPhotoPreview").style.display = "none";
        qs("plPhotoStatus").textContent = "";

        qs("plVisible").checked = false;
        qs("plHeight").value = "";
        qs("plWeight").value = "";
        qs("plFoot").value = "";
        qs("plBio").value = "";
        qs("plSkills").value = "";
        qs("plVideo").value = "";

        qs("plMatches").value = "";
        qs("plGoals").value = "";
        qs("plAssists").value = "";
        qs("plInsta").value = "";
        qs("plTwitter").value = "";
        qs("plLinkedin").value = "";
        qs("plMotto").value = "";

        // Refresh list if needed
        if (String(qs("plTeamFilter").value) === String(teamId)) {
            loadPlayersForTeam(teamId);
        } else {
            // Auto-switch filter to the added team so user sees the new player
            qs("plTeamFilter").value = teamId;
            loadPlayersForTeam(teamId);
        }

    } catch (err) {
        console.error(err);
        alert("Erreur technique: " + err.message);
    } finally {
        btnAdd.textContent = originalText;
        btnAdd.disabled = false;
    }
}

// INIT EVENTS
qs("plTeamFilter").addEventListener("change", () => {
    const teamId = parseInt(qs("plTeamFilter").value, 10) || 0;
    loadPlayersForTeam(teamId);
});

qs("btnPlAdd").addEventListener("click", addPlayer);

// UPLOAD PHOTO
const photoFile = qs("plPhotoFile");
if (photoFile) {
    photoFile.addEventListener("change", async function () {
        if (this.files.length === 0) return;

        const file = this.files[0];
        const formData = new FormData();
        formData.append("logo", file); // Reusing upload_logo.php which expects 'logo'

        const status = qs("plPhotoStatus");
        const preview = qs("plPhotoPreview");
        const hidden = qs("plPhoto");

        status.textContent = "Upload en cours...";
        status.style.color = "#FFD700";

        try {
            // On réutilise l'API de logo pour l'instant
            const res = await fetch("../../backend/api/upload_logo.php", {
                method: "POST",
                body: formData,
                credentials: "include"
            });
            const data = await res.json();

            if (data.success) {
                status.textContent = "Photo OK";
                status.style.color = "lime";
                hidden.value = data.data.url;
                preview.src = data.data.url;
                preview.style.display = "block";
            } else {
                status.textContent = "Erreur: " + data.message;
                status.style.color = "red";
            }
        } catch (err) {
            console.error(err);
            status.textContent = "Erreur upload";
            status.style.color = "red";
        }
    });
}

// CSV IMPORT LOGIC
function downloadCSVTemplate() {
    const headers = ["Nom", "Poste", "Numero", "DateNaissance (AAAA-MM-JJ)", "Licence"];
    const rows = [
        ["Jean Dupont", "Attaquant", "10", "1998-05-15", "L-2024-001"],
        ["Marie Curie", "Gardien", "1", "1995-11-07", "L-2024-002"]
    ];

    let csvContent = "data:text/csv;charset=utf-8,"
        + headers.join(";") + "\n"
        + rows.map(r => r.join(";")).join("\n");

    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "modele_joueurs_lpa.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

async function handleCSVImport() {
    const teamId = qs("plTeamSelectImport").value;
    const fileInput = qs("csvFile");
    const status = qs("importStatus");
    const btn = qs("btnImportCSV");

    if (!teamId || teamId === "") {
        alert("Veuillez sélectionner une équipe.");
        return;
    }
    if (fileInput.files.length === 0) {
        alert("Veuillez choisir un fichier CSV.");
        return;
    }

    const formData = new FormData();
    formData.append("team_id", teamId);
    formData.append("csv_file", fileInput.files[0]);

    btn.disabled = true;
    btn.textContent = "⏳ Importation en cours...";
    status.style.color = "#FFD700";
    status.textContent = "Traitement du fichier...";

    try {
        const res = await fetch(API + "players_import.php", {
            method: "POST",
            body: formData,
            credentials: "include"
        });
        const data = await res.json();

        if (data.success) {
            status.style.color = "#22c55e";
            status.textContent = "✅ " + data.message;
            fileInput.value = "";
            // Refresh list if targeting the same team
            if (qs("plTeamFilter").value === teamId) {
                loadPlayersForTeam(teamId);
            }
        } else {
            status.style.color = "#ef4444";
            status.textContent = "❌ " + data.message;
        }
    } catch (err) {
        console.error(err);
        status.style.color = "#ef4444";
        status.textContent = "Erreur technique lors de l'import.";
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-upload"></i> Lancer l\'importation';
    }
}

if (qs("btnImportCSV")) {
    qs("btnImportCSV").addEventListener("click", handleCSVImport);
}

loadTeamsPlayers();
