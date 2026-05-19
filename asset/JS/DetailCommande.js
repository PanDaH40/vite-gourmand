const detailContainer = document.getElementById("detailCommande");

const params = new URLSearchParams(window.location.search);

const numeroCommande = params.get("numero");

if (!numeroCommande) {

  detailContainer.innerHTML =
    "<p>Commande introuvable.</p>";

} else {

  fetch("php/DetailCommande.php?numero=" + numeroCommande)

    .then(function (response) {
      return response.json();
    })

    .then(function (commande) {

      if (commande.erreur) {

        detailContainer.innerHTML =
          "<p>Commande introuvable.</p>";

        return;
      }

      let materiel = "Non";

      if (commande.pret_materiel == 1) {
        materiel = "Oui";
      }

      let total =
        parseFloat(commande.prix_menu) +
        parseFloat(commande.prix_livraison);

      detailContainer.innerHTML =

        '<div class="order-header">' +

          "<div>" +

            "<h3>Commande " +
            commande.numero_commande +
            "</h3>" +

            '<p class="muted">' +
            commande.titre +
            " • " +
            commande.nombre_personne +
            " personnes</p>" +

          "</div>" +

          '<span class="badge pending">' +
          commande.statut +
          "</span>" +

        "</div>" +

        '<div class="order-details">' +

          "<p><strong>Date commande :</strong> " +
          commande.date_commande +
          "</p>" +

          "<p><strong>Date prestation :</strong> " +
          commande.date_prestation +
          "</p>" +

          "<p><strong>Heure livraison :</strong> " +
          commande.heure_livraison +
          "</p>" +

          "<p><strong>Prix menu :</strong> " +
          commande.prix_menu +
          " €</p>" +

          "<p><strong>Prix livraison :</strong> " +
          commande.prix_livraison +
          " €</p>" +

          "<p><strong>Total :</strong> " +
          total.toFixed(2) +
          " €</p>" +

          "<p><strong>Matériel prêté :</strong> " +
          materiel +
          "</p>" +

          "<p><strong>Statut :</strong> " +
          commande.statut +
          "</p>" +

        "</div>" +

        '<div class="order-actions">' +

          '<a href="MesCommandes.html" class="btn-secondary">' +
          "Retour" +
          "</a>" +

        "</div>";

    })

    .catch(function () {

      detailContainer.innerHTML =
        "<p>Impossible de charger la commande.</p>";

    });

}