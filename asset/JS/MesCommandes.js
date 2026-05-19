const commandesContainer = document.querySelector(".orders-list");

fetch("PHP/Mes_Commandes.php")
  .then(function (response) {
    return response.json();
  })
  .then(function (commandes) {
    commandesContainer.innerHTML = "";

    if (commandes.length === 0) {
      commandesContainer.innerHTML = "<p>Aucune commande trouvée.</p>";
      return;
    }

    commandes.forEach(function (commande) {
      const article = document.createElement("article");
      article.classList.add("order-card");

      let badgeClass = "pending";

      if (commande.statut === "acceptée") {
        badgeClass = "accepted";
      }

      if (commande.statut === "terminée") {
        badgeClass = "done";
      }

      const materiel = commande.pret_materiel == 1 ? "Oui" : "Non";

      const total =
        parseFloat(commande.prix_menu) + parseFloat(commande.prix_livraison);

      article.innerHTML =
        '<div class="order-header">' +
          "<div>" +
            "<h3>Commande " + commande.numero_commande + "</h3>" +
            '<p class="muted">' +
              commande.titre +
              " • " +
              commande.nombre_personne +
              " personnes" +
            "</p>" +
          "</div>" +
          '<span class="badge ' + badgeClass + '">' +
            commande.statut +
          "</span>" +
        "</div>" +

        '<div class="order-details">' +
          "<p><strong>Date prestation :</strong> " +
            commande.date_prestation +
          "</p>" +

          "<p><strong>Heure livraison :</strong> " +
            commande.heure_livraison +
          "</p>" +

          "<p><strong>Total :</strong> " +
            total.toFixed(2) +
          " €</p>" +

          "<p><strong>Matériel prêté :</strong> " +
            materiel +
          "</p>" +
        "</div>" +

        '<div class="order-actions">' +
          '<a class="btn-secondary" href="DetailCommande.html?numero=' +
            commande.numero_commande + '">Voir le détail </a>'
        "</div>";

      commandesContainer.appendChild(article);
    });
  })
  .catch(function () {
    commandesContainer.innerHTML =
      "<p>Impossible de charger les commandes.</p>";
  });