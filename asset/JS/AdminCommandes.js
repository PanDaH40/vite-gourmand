const commandesTableBody = document.getElementById("commandesTableBody");

const filterForm = document.getElementById("filterForm");
const filtreStatut = document.getElementById("statut");
const filtreDate = document.getElementById("date");
const filtreRecherche = document.getElementById("recherche");

let toutesLesCommandes = [];


/**
 * Formate une date SQL :
 * 2026-10-23 -> 23/10/2026
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
 * Retourne la classe CSS correspondant au statut.
 */
function getBadgeClass(statut) {

    const valeur = statut.toLowerCase();

    if (
        valeur === "acceptée" ||
        valeur === "en préparation" ||
        valeur === "en cours de livraison" ||
        valeur === "livré" ||
        valeur === "en attente du retour de matériel"
    ) {
        return "accepted";
    }

    if (valeur === "terminée") {
        return "done";
    }

    if (
        valeur === "refusée" ||
        valeur === "annulée"
    ) {
        return "refused";
    }

    return "pending";
}


/**
 * Affiche les commandes.
 */
function afficherCommandes(commandes) {

    commandesTableBody.innerHTML = "";

    if (commandes.length === 0) {

        commandesTableBody.innerHTML = `
            <tr>
                <td colspan="9">
                    Aucune commande trouvée.
                </td>
            </tr>
        `;

        return;
    }


    commandes.forEach(function (commande) {

        const ligne = document.createElement("tr");

        const prixMenu =
            parseFloat(commande.prix_menu) || 0;

        const prixLivraison =
            parseFloat(commande.prix_livraison) || 0;

        const total =
            prixMenu + prixLivraison;

        const materiel =
            commande.pret_materiel == 1
                ? "Oui"
                : "Non";

        const statut =
            commande.statut.toLowerCase();

        const badgeClass =
            getBadgeClass(commande.statut);

        let boutons = "";


        /**
         * EN ATTENTE
         */
        if (statut === "en attente") {

            boutons = `
                <button
                    type="button"
                    class="btn-small btn-statut"
                    data-numero="${commande.numero_commande}"
                    data-statut="acceptée">
                    Accepter
                </button>

                <button
                    type="button"
                    class="btn-small btn-danger btn-refuser"
                    data-numero="${commande.numero_commande}">
                    Refuser
                </button>
            `;
        }


        /**
         * ACCEPTÉE
         */
        else if (
            statut === "acceptée" ||
            statut === "accepté"
        ) {

            boutons = `
                <button
                    type="button"
                    class="btn-small btn-statut"
                    data-numero="${commande.numero_commande}"
                    data-statut="en préparation">
                    Mettre en préparation
                </button>
            `;
        }


        /**
         * EN PRÉPARATION
         */
        else if (statut === "en préparation") {

            boutons = `
                <button
                    type="button"
                    class="btn-small btn-statut"
                    data-numero="${commande.numero_commande}"
                    data-statut="en cours de livraison">
                    Mettre en livraison
                </button>
            `;
        }


        /**
         * EN COURS DE LIVRAISON
         */
        else if (
            statut === "en cours de livraison"
        ) {

            boutons = `
                <button
                    type="button"
                    class="btn-small btn-statut"
                    data-numero="${commande.numero_commande}"
                    data-statut="livré">
                    Marquer comme livré
                </button>
            `;
        }


        /**
         * LIVRÉ
         *
         * Avec matériel :
         * attente du retour.
         *
         * Sans matériel :
         * commande terminée.
         */
        else if (statut === "livré") {

            if (commande.pret_materiel == 1) {

                boutons = `
                    <button
                        type="button"
                        class="btn-small btn-statut"
                        data-numero="${commande.numero_commande}"
                        data-statut="en attente du retour de matériel">
                        Attendre retour matériel
                    </button>
                `;

            } else {

                boutons = `
                    <button
                        type="button"
                        class="btn-small btn-statut"
                        data-numero="${commande.numero_commande}"
                        data-statut="terminée">
                        Terminer
                    </button>
                `;
            }
        }


        /**
         * EN ATTENTE DU RETOUR DE MATÉRIEL
         */
        else if (
            statut ===
            "en attente du retour de matériel"
        ) {

            boutons = `
                <button
                    type="button"
                    class="btn-small btn-statut"
                    data-numero="${commande.numero_commande}"
                    data-statut="terminée">
                    Matériel restitué
                </button>
            `;
        }


        /**
         * TERMINÉE / REFUSÉE / ANNULÉE
         */
        else {

            boutons = `
                <span class="muted">
                    Aucune action
                </span>
            `;
        }


        ligne.innerHTML = `
            <td>
                ${commande.numero_commande}
            </td>

            <td>
                ${commande.prenom}
                <br>

                <span class="muted">
                    ${commande.email}
                </span>
            </td>

            <td>
                ${commande.titre}
            </td>

            <td>
                ${commande.nombre_personne}
            </td>

            <td>
                ${formaterDate(
                    commande.date_prestation
                )}
            </td>

            <td>
                ${total.toFixed(2)} €
            </td>

            <td>
                ${materiel}
            </td>

            <td>
                <span class="badge ${badgeClass}">
                    ${commande.statut}
                </span>
            </td>

            <td>
                <div class="table-actions">
                    ${boutons}
                </div>
            </td>
        `;

        commandesTableBody.appendChild(ligne);
    });
}


/**
 * Charge les commandes depuis MySQL.
 */
function chargerCommandes() {

    fetch("PHP/AdminCommandes.php", {
        credentials: "same-origin",
        cache: "no-store"
    })

        .then(function (response) {

            if (
                response.status === 401 ||
                response.status === 403
            ) {

                window.location.href =
                    "PagePrincipale.html";

                throw new Error(
                    "Accès refusé."
                );
            }

            if (!response.ok) {

                throw new Error(
                    "Erreur lors du chargement des commandes."
                );
            }

            return response.json();
        })

        .then(function (commandes) {

            if (commandes.erreur) {

                commandesTableBody.innerHTML = `
                    <tr>
                        <td colspan="9">
                            ${commandes.erreur}
                        </td>
                    </tr>
                `;

                return;
            }

            toutesLesCommandes = commandes;

            afficherCommandes(
                toutesLesCommandes
            );
        })

        .catch(function (error) {

            console.error(error);

            if (
                error.message !==
                "Accès refusé."
            ) {

                commandesTableBody.innerHTML = `
                    <tr>
                        <td colspan="9">
                            Impossible de charger les commandes.
                        </td>
                    </tr>
                `;
            }
        });
}


/**
 * Modifie le statut d'une commande.
 */
async function modifierStatut(
    numeroCommande,
    nouveauStatut,
    modeContact = null,
    motif = null
) {

    try {

        /**
         * Récupération de la session
         * et du jeton CSRF.
         */
        const sessionResponse =
            await fetch(
                "PHP/Check_Session.php",
                {
                    credentials: "same-origin",
                    cache: "no-store"
                }
            );

        const sessionData =
            await sessionResponse.json();


        /**
         * Administrateur OU Employé.
         */
        if (
            !sessionResponse.ok ||
            !sessionData.connecte ||
            (!sessionData.admin &&
             !sessionData.employe) ||
            !sessionData.csrf_token
        ) {

            throw new Error(
                "Session employé ou administrateur invalide."
            );
        }


        /**
         * Envoi au serveur.
         */
        const response =
            await fetch(
                "PHP/UpdateCommandeStatut.php",
                {
                    method: "POST",

                    credentials:
                        "same-origin",

                    headers: {
                        "Content-Type":
                            "application/json",

                        "X-CSRF-Token":
                            sessionData.csrf_token
                    },

                    body: JSON.stringify({
                        numero_commande:
                            numeroCommande,

                        statut:
                            nouveauStatut,

                        mode_contact:
                            modeContact,

                        motif:
                            motif
                    })
                }
            );


        const texte =
            await response.text();

        let data;


        try {

            data =
                JSON.parse(texte);

        } catch (erreur) {

            console.error(
                "Réponse reçue du serveur :",
                texte
            );

            throw new Error(
                "Le serveur n'a pas renvoyé du JSON valide."
            );
        }


        if (!response.ok) {

            throw new Error(
                data.message ||
                "Erreur serveur."
            );
        }


        if (!data.success) {

            alert(
                data.message ||
                "Impossible de modifier la commande."
            );

            return;
        }


        chargerCommandes();


    } catch (error) {

        console.error(error);

        alert(error.message);
    }
}


/**
 * Gestion des boutons.
 */
commandesTableBody.addEventListener(
    "click",
    function (event) {

        const bouton =
            event.target.closest("button");

        if (!bouton) {
            return;
        }


        const numeroCommande =
            bouton.dataset.numero;

        if (!numeroCommande) {
            return;
        }


        /**
         * REFUS D'UNE COMMANDE
         *
         * L'employé doit avoir contacté
         * le client et préciser le moyen
         * de contact ainsi que le motif.
         */
        if (
            bouton.classList.contains(
                "btn-refuser"
            )
        ) {

            const modeContact = prompt(
                "Comment avez-vous contacté le client ?\n\n" +
                "Saisissez : mail ou GSM"
            );

            if (modeContact === null) {
                return;
            }


            const contact =
                modeContact
                    .trim()
                    .toLowerCase();


            if (
                contact !== "mail" &&
                contact !== "gsm"
            ) {

                alert(
                    "Le mode de contact doit être : mail ou GSM."
                );

                return;
            }


            const motif = prompt(
                "Indiquez le motif du refus :"
            );


            if (
                motif === null ||
                motif.trim() === ""
            ) {

                alert(
                    "Le motif est obligatoire."
                );

                return;
            }


            const confirmation = confirm(
                "Confirmer le refus de la commande " +
                numeroCommande +
                " ?"
            );


            if (confirmation) {

                modifierStatut(
                    numeroCommande,
                    "refusée",
                    contact,
                    motif.trim()
                );
            }

            return;
        }


        /**
         * CHANGEMENT NORMAL DE STATUT
         */
        if (
            bouton.classList.contains(
                "btn-statut"
            )
        ) {

            const nouveauStatut =
                bouton.dataset.statut;

            if (!nouveauStatut) {
                return;
            }


            const confirmation = confirm(
                "Passer la commande " +
                numeroCommande +
                ' au statut "' +
                nouveauStatut +
                '" ?'
            );


            if (confirmation) {

                modifierStatut(
                    numeroCommande,
                    nouveauStatut
                );
            }
        }
    }
);


/**
 * Gestion des filtres.
 */
filterForm.addEventListener(
    "submit",
    function (event) {

        event.preventDefault();


        const statut =
            filtreStatut.value
                .toLowerCase();


        const date =
            filtreDate.value;


        const recherche =
            filtreRecherche.value
                .trim()
                .toLowerCase();


        const commandesFiltrees =
            toutesLesCommandes.filter(
                function (commande) {

                    let afficher = true;


                    if (
                        statut !== "" &&
                        commande.statut
                            .toLowerCase() !==
                            statut
                    ) {

                        afficher = false;
                    }


                    if (
                        date !== "" &&
                        commande.date_prestation !==
                            date
                    ) {

                        afficher = false;
                    }


                    if (recherche !== "") {

                        const numero =
                            commande.numero_commande
                                .toLowerCase();

                        const prenom =
                            commande.prenom
                                .toLowerCase();

                        const email =
                            commande.email
                                .toLowerCase();


                        if (
                            !numero.includes(
                                recherche
                            ) &&
                            !prenom.includes(
                                recherche
                            ) &&
                            !email.includes(
                                recherche
                            )
                        ) {

                            afficher = false;
                        }
                    }


                    return afficher;
                }
            );


        afficherCommandes(
            commandesFiltrees
        );
    }
);


/**
 * Chargement initial.
 */
chargerCommandes();