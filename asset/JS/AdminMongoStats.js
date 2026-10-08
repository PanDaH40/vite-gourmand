const graphiqueCommandes = document.getElementById("graphiqueCommandes");
const tableauChiffreAffaires = document.getElementById("tableauChiffreAffaires");
const filtreMenu = document.getElementById("filtreMenu");
const dateDebut = document.getElementById("dateDebut");
const dateFin = document.getElementById("dateFin");
const filtresChiffreAffaires = document.getElementById("filtresChiffreAffaires");

let statistiquesMongo = [];

// Protection contre l'insertion de HTML dans la page
function echapperHTML(valeur) {
  return String(valeur ?? "").replace(/[&<>"']/g, function (caractere) {
    return {
      "&": "&amp;",
      "<": "&lt;",
      ">": "&gt;",
      '"': "&quot;",
      "'": "&#39;"
    }[caractere];
  });
}

// Affichage du nombre de commandes par menu
function afficherGraphique(statistiques) {
  graphiqueCommandes.innerHTML = "";

  if (statistiques.length === 0) {
    graphiqueCommandes.textContent = "Aucune commande à afficher.";
    return;
  }

  const maximum = Math.max(
    1,
    ...statistiques.map(stat => Number(stat.nombre_commandes) || 0)
  );

  statistiques.forEach(function (stat) {
    const nombre = Number(stat.nombre_commandes) || 0;
    const pourcentage = (nombre / maximum) * 100;

    const ligne = document.createElement("div");
    ligne.style.marginBottom = "15px";

    const titre = document.createElement("p");
    titre.textContent = `${stat.titre} : ${nombre} commande(s)`;

    const fond = document.createElement("div");
    fond.style.backgroundColor = "#e5e7eb";
    fond.style.borderRadius = "5px";
    fond.style.height = "22px";

    const barre = document.createElement("div");
    barre.style.backgroundColor = "#198754";
    barre.style.width = `${pourcentage}%`;
    barre.style.height = "100%";
    barre.style.borderRadius = "5px";

    fond.appendChild(barre);
    ligne.appendChild(titre);
    ligne.appendChild(fond);
    graphiqueCommandes.appendChild(ligne);
  });
}

// Affichage du chiffre d'affaires
function afficherChiffreAffaires(statistiques) {
  tableauChiffreAffaires.innerHTML = "";

  if (statistiques.length === 0) {
    tableauChiffreAffaires.innerHTML =
      '<tr><td colspan="3">Aucun résultat.</td></tr>';
    return;
  }

  statistiques.forEach(function (stat) {
    const ligne = document.createElement("tr");

    const chiffreAffaires = Number(stat.chiffre_affaires) || 0;

    ligne.innerHTML = `
      <td>${echapperHTML(stat.titre)}</td>
      <td>${Number(stat.nombre_commandes) || 0}</td>
      <td>${chiffreAffaires.toFixed(2)} €</td>
    `;

    tableauChiffreAffaires.appendChild(ligne);
  });
}

// Remplissage de la liste des menus
function remplirFiltreMenus(statistiques) {
  filtreMenu.innerHTML = '<option value="">Tous les menus</option>';

  statistiques.forEach(function (stat) {
    const option = document.createElement("option");
    option.value = String(stat.menu_id);
    option.textContent = stat.titre;
    filtreMenu.appendChild(option);
  });
}

// Chargement des statistiques depuis MongoDB
async function chargerStatistiquesMongo() {
  try {
    const parametres = new URLSearchParams();

    if (filtreMenu.value) {
      parametres.set("menu_id", filtreMenu.value);
    }

    if (dateDebut.value) {
      parametres.set("date_debut", dateDebut.value);
    }

    if (dateFin.value) {
      parametres.set("date_fin", dateFin.value);
    }

    const url = "PHP/MongoStats.php?" + parametres.toString();

    const reponse = await fetch(url);

    if (!reponse.ok) {
      throw new Error("Impossible de charger les statistiques MongoDB.");
    }

    const donnees = await reponse.json();

    if (!donnees.success) {
      throw new Error(donnees.message || "Erreur MongoDB.");
    }

    statistiquesMongo = donnees.statistiques || [];

    afficherGraphique(statistiquesMongo);
    afficherChiffreAffaires(statistiquesMongo);

  } catch (erreur) {
    console.error(erreur);
    graphiqueCommandes.textContent = "Impossible de charger le graphique.";
    tableauChiffreAffaires.innerHTML =
      '<tr><td colspan="3">Impossible de charger les statistiques.</td></tr>';
  }
}

// Premier chargement : récupération des menus
async function initialiserStatistiquesMongo() {
  await chargerStatistiquesMongo();
  remplirFiltreMenus(statistiquesMongo);
}

// Application des filtres
filtresChiffreAffaires.addEventListener("submit", function (evenement) {
  evenement.preventDefault();

  if (dateDebut.value && dateFin.value && dateDebut.value > dateFin.value) {
    alert("La date de début doit être antérieure à la date de fin.");
    return;
  }

  chargerStatistiquesMongo();
});

initialiserStatistiquesMongo();