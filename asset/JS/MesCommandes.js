const commandesContainer = document.querySelector(".orders-list");


/**
 * Transforme une date SQL (2026-10-23)
 * en date française (23/10/2026).
 */
function formaterDate(dateSQL) {

    if (!dateSQL) {
        return "";
    }

    const morceaux = dateSQL.split("-");

    if (morceaux.length !== 3) {
        return dateSQL;
    }

    return morceaux[2] + "/" + morceaux[1] + "/" + morceaux[0];
}


/**
 * Charge les commandes de l'utilisateur connecté.
 */
fetch("PHP/Mes_Commandes.php")

    .then(function (response) {

        if (response.status === 401) {

            window.location.href = "Connection.html";

            throw new Error("Utilisateur non connecté.");
        }

        if (!response.ok) {

            throw new Error(
                "Erreur lors du chargement des commandes."
            );
        }

        return response.json();
    })

    .then(function (commandes) {

        commandesContainer.innerHTML = "";


        /**
         * Aucune commande.
         */
        if (commandes.length === 0) {

            commandesContainer.innerHTML =
                "<p>Aucune commande trouvée.</p>";

            return;
        }


        /**
         * Création d'une carte pour chaque commande.
         */
        commandes.forEach(function (commande) {

            const article =
                document.createElement("article");

            article.classList.add("order-card");


            /**
             * Gestion du statut.
             */
            let badgeClass = "pending";

            const statut =
                commande.statut.toLowerCase();


            const estAcceptee =
                statut === "acceptée" ||
                statut === "acceptee" ||
                statut === "accepté" ||
                statut === "accepte";


            const estTerminee =
                statut === "terminée" ||
                statut === "terminee" ||
                statut === "terminé" ||
                statut === "termine";


            if (estAcceptee) {
                badgeClass = "accepted";
            }


            if (estTerminee) {
                badgeClass = "done";
            }


            /**
             * Matériel prêté.
             */
            const materiel =
                commande.pret_materiel == 1
                    ? "Oui"
                    : "Non";


            /**
             * Calcul du total.
             */
            const prixMenu =
                parseFloat(commande.prix_menu) || 0;

            const livraison =
                parseFloat(commande.prix_livraison) || 0;

            const total =
                prixMenu + livraison;


            /**
             * Actions disponibles.
             */
            let actions =

                '<a class="btn-secondary" ' +

                'href="DetailCommande.html?numero=' +

                encodeURIComponent(
                    commande.numero_commande
                ) +

                '">' +

                "Voir le détail" +

                "</a>";


            /**
             * Une commande terminée permet
             * de laisser un avis.
             */
            if (estTerminee) {

                actions +=

                    ' <button type="button" ' +

                    'class="btn-secondary btn-avis" ' +

                    'data-commande="' +

                    encodeURIComponent(
                        commande.numero_commande
                    ) +

                    '">' +

                    "Laisser un avis" +

                    "</button>";
            }


            /**
             * Création de la carte.
             */
            article.innerHTML =

                '<div class="order-header">' +

                    "<div>" +

                        "<h3>Commande " +
                            commande.numero_commande +
                        "</h3>" +

                        '<p class="muted">' +
                            commande.titre +
                            " • " +
                            commande.nombre_personne +
                            " personnes" +
                        "</p>" +

                    "</div>" +

                    '<span class="badge ' +
                        badgeClass +
                    '">' +

                        commande.statut +

                    "</span>" +

                "</div>" +


                '<div class="order-details">' +

                    "<p><strong>Date prestation :</strong> " +
                        formaterDate(
                            commande.date_prestation
                        ) +
                    "</p>" +

                    "<p><strong>Heure livraison :</strong> " +
                        commande.heure_livraison +
                    "</p>" +

                    "<p><strong>Prix menu :</strong> " +
                        prixMenu.toFixed(2) +
                    " €</p>" +

                    "<p><strong>Livraison :</strong> " +
                        livraison.toFixed(2) +
                    " €</p>" +

                    "<p><strong>Total :</strong> " +
                        total.toFixed(2) +
                    " €</p>" +

                    "<p><strong>Matériel prêté :</strong> " +
                        materiel +
                    "</p>" +

                "</div>" +


                '<div class="order-actions">' +

                    actions +

                "</div>";


            commandesContainer.appendChild(article);

        });

    })

    .catch(function (error) {

        console.error(error);

        if (
            !error.message.includes(
                "Utilisateur non connecté"
            )
        ) {

            commandesContainer.innerHTML =
                "<p>Impossible de charger les commandes.</p>";
        }

    });


/**
 * Clic sur "Laisser un avis".
 */
commandesContainer.addEventListener(
    "click",
    function (event) {

        const bouton =
            event.target.closest(".btn-avis");


        if (!bouton) {
            return;
        }


        const numeroCommande =
            bouton.dataset.commande;


        window.location.href =
            "Avis.html?commande=" +
            numeroCommande;
    }
);