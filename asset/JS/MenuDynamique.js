const cardsContainer = document.querySelector(".cards");
const menuResultCount = document.getElementById("result-count");

fetch("PHP/Get_Menus.php")
  .then(function (response) {
    if (!response.ok) {
      throw new Error("Erreur HTTP : " + response.status);
    }

    return response.json();
  })
  .then(function (menus) {
    cardsContainer.innerHTML = "";

    menus.forEach(function (menu) {
      const article = document.createElement("article");

      article.classList.add("card");

      // Détermine le thème du menu
      let themeMenu = "Classique";

      if (menu.titre.toLowerCase().includes("pâques")) {
        themeMenu = "Pâques";
      } else if (menu.titre.toLowerCase().includes("noël")) {
        themeMenu = "Noël";
      }

      // Données utilisées par les filtres
      article.dataset.theme = themeMenu;
      article.dataset.regime = menu.regime;
      article.dataset.min = menu.nombre_personne_minimum;
      article.dataset.price = menu.prix_par_personne;

      article.innerHTML =
        "<h3>" + menu.titre + "</h3>" +
        '<p class="description">' + menu.description + "</p>" +

        '<div class="meta">' +
          "<span>Minimum : " +
            menu.nombre_personne_minimum +
            " pers.</span>" +

          "<span>" +
            menu.prix_par_personne +
            " €/pers.</span>" +
        "</div>" +

        '<div class="actions">' +

          '<a href="DetailMenu.html?id=' +
            menu.menu_id +
            '" class="btn-outline">Voir le détail</a>' +

          '<a href="Commande.html?menu=' +
            menu.menu_id +
            '" class="btn-outline">Commander</a>' +

        "</div>";

      cardsContainer.appendChild(article);
    });

    menuResultCount.textContent =
      menus.length + " menu(s) affiché(s)";

    // Relance les filtres une fois les menus créés
    if (typeof filtrerMenus === "function") {
      filtrerMenus();
    }
  })
  .catch(function (error) {
    console.error(
      "Erreur lors du chargement des menus :",
      error
    );
  });