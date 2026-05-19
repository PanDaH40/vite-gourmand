const cardsContainer = document.querySelector(".cards");
const resultCount = document.getElementById("result-count");

fetch("php/get_menus.php")
  .then(function (response) {
    return response.json();
  })
  .then(function (menus) {
    cardsContainer.innerHTML = "";

    menus.forEach(function (menu) {
      const article = document.createElement("article");

      article.classList.add("card");

      article.dataset.theme = "Classique";
      article.dataset.regime = menu.regime;
      article.dataset.min = menu.nombre_personne_minimum;
      article.dataset.price = menu.prix_par_personne;

      article.innerHTML =
        "<h3>" + menu.titre + "</h3>" +
        '<p class="description">' + menu.description + "</p>" +
        '<div class="meta">' +
          "<span>Minimum : " + menu.nombre_personne_minimum + " pers.</span>" +
          "<span>" + menu.prix_par_personne + " €/pers.</span>" +
        "</div>" +
        '<a href="Commande.html" class="btn-outline">Commander</a>';

      cardsContainer.appendChild(article);
    });

    resultCount.textContent = menus.length + " menu(s) affiché(s)";

    if (typeof filtrerMenus === "function") {
      filtrerMenus();
    }
  });