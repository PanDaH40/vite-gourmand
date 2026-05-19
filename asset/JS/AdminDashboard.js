const commandesAttente = document.getElementById("commandesAttente");
const commandesAcceptees = document.getElementById("commandesAcceptees");
const menusDisponibles = document.getElementById("menusDisponibles");
const materielRecuperer = document.getElementById("materielRecuperer");

fetch("php/AdminStats.php")
  .then(function (response) {
    return response.json();
  })
  .then(function (stats) {
    if (stats.erreur) {
      return;
    }

    commandesAttente.textContent = stats.commandes_attente;
    commandesAcceptees.textContent = stats.commandes_acceptees;
    menusDisponibles.textContent = stats.menus_disponibles;
    materielRecuperer.textContent = stats.materiel_a_recuperer;
  })
  .catch(function () {
    console.log("Impossible de charger les statistiques admin.");
  });