const menuSelect = document.getElementById("menuSelect");
const nbPersonnes = document.getElementById("nbPersonnes");
const ville = document.getElementById("ville");

const nbVegetarien = document.getElementById("nbVegetarien");
const nbVegan = document.getElementById("nbVegan");

const adapteInfo = document.getElementById("adapteInfo");

const prixMenu = document.getElementById("prixMenu");
const reduction = document.getElementById("reduction");
const prixLivraison = document.getElementById("prixLivraison");
const total = document.getElementById("total");

const form = document.querySelector(".commande-form");

function getMenuData() {
  const option = menuSelect.options[menuSelect.selectedIndex];

  let minPersons = parseInt(option.dataset.min);
  let prixPersonne = parseFloat(option.dataset.prix);

  if (isNaN(minPersons)) {
    minPersons = 1;
  }

  if (isNaN(prixPersonne)) {
    prixPersonne = 0;
  }

  return {
    minPersons: minPersons,
    prixPersonne: prixPersonne
  };
}

function calculer() {
  const menu = getMenuData();

  let nb = parseInt(nbPersonnes.value);

  if (isNaN(nb)) {
    nb = 0;
  }

  if (nb > 0 && nb < menu.minPersons) {
    nb = menu.minPersons;
    nbPersonnes.value = nb;
  }

  let prix = nb * menu.prixPersonne;

  let reduc = 0;

  if (nb >= menu.minPersons + 5) {
    reduc = prix * 0.10;
  }

  let livraison = 5;

  if (ville.value.trim().toLowerCase() === "bordeaux") {
    livraison = 0;
  }

  let totalPrix = prix - reduc + livraison;

  prixMenu.textContent = prix.toFixed(2);
  reduction.textContent = reduc.toFixed(2);
  prixLivraison.textContent = livraison.toFixed(2);
  total.textContent = totalPrix.toFixed(2);
}

function updateAdapteInfo() {
  let totalPersonnes = parseInt(nbPersonnes.value);

  if (isNaN(totalPersonnes)) {
    totalPersonnes = 0;
  }

  let vegetarien = parseInt(nbVegetarien.value);
  let vegan = parseInt(nbVegan.value);

  if (isNaN(vegetarien)) {
    vegetarien = 0;
  }

  if (isNaN(vegan)) {
    vegan = 0;
  }

  if (vegetarien + vegan > totalPersonnes) {
    vegan = 0;
    vegetarien = totalPersonnes;
  }

  nbVegetarien.value = vegetarien;
  nbVegan.value = vegan;

  let classique = totalPersonnes - (vegetarien + vegan);

  adapteInfo.textContent =
    "Végétariens : " + vegetarien +
    " • Vegans : " + vegan +
    " • Classiques : " + classique;
}

function verifierFormulaire(e) {
  const menu = getMenuData();

  if (nbPersonnes.value.trim() === "") {
    e.preventDefault();
    alert("Veuillez indiquer le nombre de personnes.");
    return;
  }

  if (parseInt(nbPersonnes.value) < menu.minPersons) {
    e.preventDefault();
    alert("Le nombre minimum de personnes pour ce menu est de " + menu.minPersons + ".");
    return;
  }

  if (ville.value.trim() === "") {
    e.preventDefault();
    alert("Veuillez renseigner la ville de livraison.");
    return;
  }

  updateAdapteInfo();
  calculer();
}

menuSelect.addEventListener("change", function () {
  calculer();
  updateAdapteInfo();
});

nbPersonnes.addEventListener("input", function () {
  calculer();
  updateAdapteInfo();
});

ville.addEventListener("input", calculer);

nbVegetarien.addEventListener("input", updateAdapteInfo);
nbVegan.addEventListener("input", updateAdapteInfo);

form.addEventListener("submit", verifierFormulaire);

calculer();
updateAdapteInfo();