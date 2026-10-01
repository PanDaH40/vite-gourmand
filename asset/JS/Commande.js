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


/**
 * Récupère les informations du menu sélectionné.
 */
function getMenuData() {

  const option = menuSelect.options[menuSelect.selectedIndex];

  if (!option || option.value === "") {
    return {
      minPersons: 1,
      prixPersonne: 0
    };
  }

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


/**
 * Charge les menus depuis MySQL grâce à PHP.
 */
function chargerMenus() {

  fetch("PHP/Get_Menus.php")

    .then(function (response) {

      if (!response.ok) {
        throw new Error("Impossible de charger les menus.");
      }

      return response.json();
    })

    .then(function (menus) {

      menuSelect.innerHTML = "";

      menus.forEach(function (menu) {

        const option = document.createElement("option");

        option.value = menu.menu_id;
        option.textContent = menu.titre;

        option.dataset.min = menu.nombre_personne_minimum;
        option.dataset.prix = menu.prix_par_personne;

        menuSelect.appendChild(option);
      });


      /**
       * Récupère le menu présent dans l'URL.
       *
       * Exemple :
       * Commande.html?menu=4
       */
      const params = new URLSearchParams(window.location.search);
      const menuId = params.get("menu");

      if (menuId) {

        const optionExiste = Array.from(menuSelect.options).some(
          function (option) {
            return option.value === menuId;
          }
        );

        if (optionExiste) {
          menuSelect.value = menuId;
        }
      }


      mettreAJourMinimum();

      calculer();
      updateAdapteInfo();
    })

    .catch(function (error) {

      console.error(
        "Erreur lors du chargement des menus :",
        error
      );

      menuSelect.innerHTML =
        '<option value="">Erreur de chargement</option>';
    });
}


/**
 * Adapte le minimum du champ "nombre de personnes"
 * selon le menu sélectionné.
 */
function mettreAJourMinimum() {

  const menu = getMenuData();

  nbPersonnes.min = menu.minPersons;

  /**
   * Cette vérification est utile uniquement
   * lorsqu'on change de menu.
   */
  if (
    nbPersonnes.value !== "" &&
    parseInt(nbPersonnes.value) < menu.minPersons
  ) {
    nbPersonnes.value = menu.minPersons;
  }
}


/**
 * Calcule le prix de la commande.
 */
function calculer() {

  const menu = getMenuData();

  let nb = parseInt(nbPersonnes.value);

  if (isNaN(nb)) {
    nb = 0;
  }


  /**
   * Prix avant réduction.
   */
  let prix = nb * menu.prixPersonne;


  /**
   * Réduction de 10 %
   * si le nombre de personnes atteint
   * le minimum du menu + 5 personnes.
   */
  let reduc = 0;

  if (nb >= menu.minPersons + 5) {
    reduc = prix * 0.10;
  }


  /**
   * Livraison :
   * Bordeaux = gratuite
   * autre ville = 5 €
   */
  let livraison = 5;

  if (ville.value.trim().toLowerCase() === "bordeaux") {
    livraison = 0;
  }


  /**
   * Calcul du total.
   */
  const totalPrix = prix - reduc + livraison;


  /**
   * Affichage.
   */
  prixMenu.textContent = prix.toFixed(2);
  reduction.textContent = reduc.toFixed(2);
  prixLivraison.textContent = livraison.toFixed(2);
  total.textContent = totalPrix.toFixed(2);
}


/**
 * Répartition des menus :
 * classique / végétarien / vegan.
 */
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


  /**
   * On empêche d'avoir plus de menus adaptés
   * que de personnes.
   */
  if (vegetarien + vegan > totalPersonnes) {

    vegan = 0;
    vegetarien = totalPersonnes;
  }


  nbVegetarien.value = vegetarien;
  nbVegan.value = vegan;


  const classique =
    totalPersonnes - (vegetarien + vegan);


  adapteInfo.textContent =
    "Végétariens : " + vegetarien +
    " • Vegans : " + vegan +
    " • Classiques : " + classique;
}


/**
 * Vérification du formulaire avant envoi.
 */
function verifierFormulaire(e) {

  const menu = getMenuData();


  /**
   * Vérifie qu'un menu est sélectionné.
   */
  if (menuSelect.value === "") {

    e.preventDefault();

    alert("Veuillez sélectionner un menu.");

    return;
  }


  /**
   * Vérifie le nombre de personnes.
   */
  if (nbPersonnes.value.trim() === "") {

    e.preventDefault();

    alert("Veuillez indiquer le nombre de personnes.");

    return;
  }


  /**
   * Vérifie le minimum demandé par le menu.
   */
  if (parseInt(nbPersonnes.value) < menu.minPersons) {

    e.preventDefault();

    alert(
      "Le nombre minimum de personnes pour ce menu est de " +
      menu.minPersons +
      "."
    );

    return;
  }


  /**
   * Vérifie la ville.
   */
  if (ville.value.trim() === "") {

    e.preventDefault();

    alert("Veuillez renseigner la ville de livraison.");

    return;
  }


  updateAdapteInfo();
  calculer();
}


/**
 * Changement de menu.
 */
menuSelect.addEventListener("change", function () {

  mettreAJourMinimum();
  calculer();
  updateAdapteInfo();
});


/**
 * Modification du nombre de personnes.
 */
nbPersonnes.addEventListener("input", function () {

  calculer();
  updateAdapteInfo();
});


/**
 * Modification de la ville.
 */
ville.addEventListener(
  "input",
  calculer
);


/**
 * Modification du nombre de végétariens.
 */
nbVegetarien.addEventListener(
  "input",
  updateAdapteInfo
);


/**
 * Modification du nombre de vegans.
 */
nbVegan.addEventListener(
  "input",
  updateAdapteInfo
);


/**
 * Validation du formulaire.
 */
form.addEventListener(
  "submit",
  verifierFormulaire
);


/**
 * Chargement initial des menus.
 */
chargerMenus();