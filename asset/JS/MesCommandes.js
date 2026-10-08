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
 * Transforme une date/heure SQL
 * (2026-10-07 06:23:51)
 * en 07/10/2026 à 06:23.
 */
function formaterDateHeure(dateSQL) {

    if (!dateSQL) {
        return "";
    }

    const parties = dateSQL.split(" ");

    if (parties.length !== 2) {
        return dateSQL;
    }

    const date = formaterDate(parties[0]);

    const heureComplete = parties[1].split(":");

    const heure =
        heureComplete[0] + ":" + heureComplete[1];

    return date + " à " + heure;
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
             * Une commande en attente
             * peut être annulée par le client.
             */
            if (statut === "en attente") {

                actions +=

                    ' <button type="button" ' +

                    'class="btn-secondary btn-annuler" ' +

                    'data-commande="' +

                    encodeURIComponent(
                        commande.numero_commande
                    ) +

                    '">' +

                    "Annuler la commande" +

                    "</button>";
            }


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
             * Historique de la commande.
             */
            let historiqueHtml = "";

            if (
                Array.isArray(commande.historique) &&
                commande.historique.length > 0
            ) {

                historiqueHtml =
                    '<div class="order-history">' +
                    "<h4>Suivi de la commande</h4>" +
                    "<ul>";


                commande.historique.forEach(
                    function (historique) {

                        historiqueHtml +=
                            "<li>" +
                            formaterDateHeure(
                                historique.date_modification
                            ) +
                            " — " +
                            historique.statut +
                            "</li>";
                    }
                );


                historiqueHtml +=
                    "</ul>" +
                    "</div>";

            } else {

                historiqueHtml =
                    '<div class="order-history">' +
                    "<h4>Suivi de la commande</h4>" +
                    "<p>Aucun historique disponible.</p>" +
                    "</div>";
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


                historiqueHtml +


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


/**
 * Clic sur "Annuler la commande".
 */
commandesContainer.addEventListener(
    "click",
    async function (event) {

        const bouton =
            event.target.closest(".btn-annuler");

        if (!bouton || bouton.disabled) {
            return;
        }

        const numeroCommande =
            decodeURIComponent(
                bouton.dataset.commande
            );

        const confirmation = confirm(
            "Voulez-vous vraiment annuler la commande " +
            numeroCommande +
            " ?"
        );

        if (!confirmation) {
            return;
        }

        // Empêche plusieurs clics pendant la requête.
        bouton.disabled = true;

        try {

            /**
             * Récupération du jeton CSRF.
             */
            const sessionResponse = await fetch(
                "PHP/Check_Session.php",
                {
                    credentials: "same-origin",
                    cache: "no-store"
                }
            );

            if (!sessionResponse.ok) {

                throw new Error(
                    "Impossible de vérifier la session."
                );
            }

            const session =
                await sessionResponse.json();

            if (
                !session.connecte ||
                !session.csrf_token
            ) {

                throw new Error(
                    "Session expirée ou jeton de sécurité absent."
                );
            }


            /**
             * Envoi de la demande d'annulation.
             */
            const response = await fetch(
                "PHP/AnnulerCommande.php",
                {
                    method: "POST",
                    credentials: "same-origin",

                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-Token":
                            session.csrf_token
                    },

                    body: JSON.stringify({
                        numero_commande:
                            numeroCommande
                    })
                }
            );


            /**
             * Lecture de la réponse PHP.
             */
            const resultat =
                await response.json();

            if (
                !response.ok ||
                !resultat.success
            ) {

                throw new Error(
                    resultat.message ||
                    "Impossible d'annuler la commande."
                );
            }


            /**
             * Confirmation et actualisation.
             */
            alert(resultat.message);

            window.location.reload();

        } catch (error) {

            console.error(error);

            alert(
                error.message ||
                "Une erreur est survenue."
            );

            bouton.disabled = false;
        }
    }
);