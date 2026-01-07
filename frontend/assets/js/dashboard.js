// frontend/assets/js/dashboard.js

const API = "../../backend/api/";

function qs(id) {
    return document.getElementById(id);
}

/* ============================================================
   1️⃣  CHARGEMENT DES CATÉGORIES
   ============================================================ */
async function loadCategories() {
    const res = await fetch(API + "categories_list.php", {
        credentials: "include"
    });
    const data = await res.json();

    if (!data.success) {
        alert("Erreur chargement catégories : " + data.message);
        return;
    }

    data.data.forEach(cat => {
        const opt = document.createElement("option");
        opt.value = cat.id;
        opt.textContent = `${cat.name} (${cat.season})`;
        qs("categorySelect").appendChild(opt);
    });
}

/* ============================================================
   2️⃣  AJUSTEMENT AUTOMATIQUE POUR LE CHAMPIONNAT
   ============================================================ */
qs("competitionType")?.addEventListener("change", () => {
    const type = qs("competitionType").value;
    const pouleField = qs("pouleCount");

    if (type === "league") {
        pouleField.value = 1;
        pouleField.disabled = true;
    } else {
        pouleField.disabled = false;
    }
});

/* ============================================================
   3️⃣  ANIMATION UEFA AVEC AUDIO
   ============================================================ */
async function animateUEFA(draw) {
    const container = qs("uefaAnimation");
    const audio = qs("uefaAudio");

    container.innerHTML = "<h2>🎬 Tirage en cours...</h2>";
    container.classList.add("uefa-active");

    // Musique UEFA
    if (audio) {
        try {
            audio.currentTime = 0;
            audio.play();
        } catch (e) {
            console.log("Audio bloqué :", e);
        }
    }

    function sleep(ms) {
        return new Promise(r => setTimeout(r, ms));
    }

    for (const p of draw.poules) {

        const pouleTitle = document.createElement("h3");
        pouleTitle.style.marginTop = "18px";
        pouleTitle.textContent = p.name;
        container.appendChild(pouleTitle);

        await sleep(600);

        for (const team of p.teams) {
            // Boule tournante
            const ball = document.createElement("div");
            ball.className = "ball";
            container.appendChild(ball);

            await sleep(900);

            // Révélation
            const row = document.createElement("div");
            row.className = "reveal flash";

            row.style.display = "flex";
            row.style.alignItems = "center";
            row.style.gap = "10px";
            row.style.marginBottom = "10px";

            const logo = document.createElement("img");
            logo.src = team.logo_url || "../assets/img/default.png";
            logo.width = 45;
            logo.height = 45;
            logo.style.borderRadius = "50%";
            logo.style.border = "2px solid #ddd";

            const name = document.createElement("strong");
            name.textContent = team.name;

            row.appendChild(logo);
            row.appendChild(name);
            container.appendChild(row);

            setTimeout(() => row.classList.remove("flash"), 600);

            await sleep(700);
        }
    }

    container.classList.remove("uefa-active");

    // Stop audio
    if (audio) {
        setTimeout(() => {
            audio.pause();
            audio.currentTime = 0;
        }, 1000);
    }
}

/* ============================================================
   4️⃣  LANCER LE TIRAGE
   ============================================================ */
qs("btnDraw").onclick = async () => {

    const category_id = parseInt(qs("categorySelect").value, 10);
    const nb_poules = parseInt(qs("pouleCount").value, 10);
    const type = qs("competitionType").value; // NEW 🔥

    if (!category_id || !nb_poules) {
        alert("Veuillez sélectionner une catégorie et un nombre de poules.");
        return;
    }

    // Force 1 poule si championnat
    let finalPouleCount = nb_poules;
    if (type === "league") finalPouleCount = 1;

    const payload = {
        category_id,
        nb_poules: finalPouleCount,
        type: type
    };

    const res = await fetch(API + "draw_create.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        credentials: "include",
        body: JSON.stringify(payload)
    });

    const data = await res.json();

    if (!data.success) {
        alert("Erreur : " + data.message);
        return;
    }

    const container = qs("uefaAnimation");
    container.innerHTML = "";

    animateUEFA(data.data);
};

/* ============================================================
   5️⃣  NOTIFICATIONS
   ============================================================ */
function toggleNotifPanel() {
    const p = qs("notifPanel");
    p.style.display = (p.style.display === 'none') ? 'block' : 'none';
}

async function checkNotifications() {
    try {
        const res = await fetch(API + "notifications_list.php?role=admin");
        const data = await res.json();
        const badge = qs("notifBadge");
        const list = qs("notifList");

        if (data.success && data.data.length > 0) {
            badge.innerText = data.data.length;
            badge.style.display = 'block';
            list.innerHTML = data.data.map(n => `
                <div style="padding:8px 0; border-bottom:1px solid #ffffff11;">
                    <i class="fas fa-info-circle" style="color:#FFD700;"></i> ${n.message}
                </div>
            `).join('');

            // Notification sonore si nouveau
            if (badge.dataset.count < data.data.length) {
                new Audio('../assets/audio/notif.mp3').play().catch(() => { });
            }
            badge.dataset.count = data.data.length;
        } else {
            badge.style.display = 'none';
            list.innerHTML = "<p style='opacity:0.5;'>Aucune nouvelle notification.</p>";
            badge.dataset.count = 0;
        }
    } catch (e) { }
}

setInterval(checkNotifications, 10000); // Toutes les 10s

/* ============================================================
   6️⃣  INIT
   ============================================================ */
loadCategories();
checkNotifications();
