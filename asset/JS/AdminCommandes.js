const commandesTableBody = document.getElementById("commandesTableBody");
const filterForm = document.getElementById("filterForm");
const filtreStatut = document.getElementById("statut");
const filtreDate = document.getElementById("date");
const filtreRecherche = document.getElementById("recherche");

let toutesLesCommandes = [];


/*
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


/*
 * Retourne la classe CSS correspondant au statut.
 */
function getBadgeClass(statut) {

    const valeur = statut.toLowerCase();

    if (
        valeur === "acceptée" ||
        valeur === "acceptee" ||
        valeur === "accepté" ||
        valeur === "accepte"
    ) {
        return "accepted";
    }

    if (
        valeur === "terminée" ||
        valeur === "terminee" ||
        valeur === "terminé" ||
        valeur === "termine"
    ) {
        return "done";
    }

    if (
        valeur === "refusée" ||
        valeur === "refusee" ||
        valeur === "refusé" ||
        valeur === "refuse"
    ) {
        return "refused";
    }

    return "pending";
}


/*
 * Affiche les commandes dans le tableau.
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

        const prixMenu = parseFloat(commande.prix_menu) || 0;
        const prixLivraison = parseFloat(commande.prix_livraison) || 0;
        const total = prixMenu + prixLivraison;

        const materiel =
            commande.pret_materiel == 1
                ? "Oui"
                : "Non";

        const statut = commande.statut.toLowerCase();

        const badgeClass = getBadgeClass(commande.statut);

        let boutons = "";


        /*
         * Commande en attente
         */
        if (statut === "en attente") {

            boutons = `
                <button
                    type="button"
                    class="btn-small btn-accepter"
                    data-numero="${commande.numero_commande}">
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


        /*
         * Commande acceptée
         */
        else if (
            statut === "acceptée" ||
            statut === "acceptee"
        ) {

            boutons = `
                <button
                    type="button"
                    class="btn-small btn-terminer"
                    data-numero="${commande.numero_commande}">
                    Terminer
                </button>
            `;
        }


        /*
         * Commande refusée ou terminée
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
                ${formaterDate(commande.date_prestation)}
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


/*
 * Charge les commandes depuis MySQL
 * grâce à AdminCommandes.php.
 */
function chargerCommandes() {

    fetch("PHP/AdminCommandes.php")

        .then(function (response) {

            if (response.status === 401 || response.status === 403) {

                window.location.href = "PagePrincipale.html";

                throw new Error("Accès administrateur refusé.");
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

            afficherCommandes(toutesLesCommandes);
        })

        .catch(function (error) {

            console.error(error);

            if (
                error.message !==
                "Accès administrateur refusé."
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


/*
 * Modifie le statut d'une commande.
 */
function modifierStatut(numeroCommande, nouveauStatut) {

    fetch("PHP/UpdateCommandeStatut.php", {

        method: "POST",

        headers: {
            "Content-Type": "application/json"
        },

        body: JSON.stringify({
            numero_commande: numeroCommande,
            statut: nouveauStatut
        })
    })

        .then(function (response) {

            /*
             * On récupère d'abord le contenu en texte.
             *
             * Cela nous permettra de voir clairement
             * une éventuelle erreur PHP au lieu d'avoir
             * simplement "Unexpected token <".
             */
            return response.text().then(function (texte) {

                let data;

                try {

                    data = JSON.parse(texte);

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

                return data;
            });
        })

        .then(function (data) {

            if (!data.success) {

                alert(
                    data.message ||
                    "Impossible de modifier la commande."
                );

                return;
            }

            /*
             * Recharge les données depuis MySQL.
             */
            chargerCommandes();
        })

        .catch(function (error) {

            console.error(error);

            alert(error.message);
        });
}


/*
 * Gestion des boutons dynamiques.
 */
commandesTableBody.addEventListener(
    "click",
    function (event) {

        const bouton = event.target.closest("button");

        if (!bouton) {
            return;
        }

        const numeroCommande =
            bouton.dataset.numero;

        if (!numeroCommande) {
            return;
        }


        /*
         * ACCEPTER
         */
        if (
            bouton.classList.contains("btn-accepter")
        ) {

            const confirmation = confirm(
                "Accepter la commande " +
                numeroCommande +
                " ?"
            );

            if (confirmation) {

                modifierStatut(
                    numeroCommande,
                    "acceptée"
                );
            }

            return;
        }


        /*
         * REFUSER
         */
        if (
            bouton.classList.contains("btn-refuser")
        ) {

            const confirmation = confirm(
                "Refuser la commande " +
                numeroCommande +
                " ?"
            );

            if (confirmation) {

                modifierStatut(
                    numeroCommande,
                    "refusée"
                );
            }

            return;
        }


        /*
         * TERMINER
         */
        if (
            bouton.classList.contains("btn-terminer")
        ) {

            const confirmation = confirm(
                "Marquer la commande " +
                numeroCommande +
                " comme terminée ?"
            );

            if (confirmation) {

                modifierStatut(
                    numeroCommande,
                    "terminée"
                );
            }
        }
    }
);


/*
 * Gestion des filtres.
 */
filterForm.addEventListener(
    "submit",
    function (event) {

        event.preventDefault();

        const statut =
            filtreStatut.value.toLowerCase();

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
                        commande.statut.toLowerCase() !== statut
                    ) {

                        afficher = false;
                    }


                    if (
                        date !== "" &&
                        commande.date_prestation !== date
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
                            !numero.includes(recherche) &&
                            !prenom.includes(recherche) &&
                            !email.includes(recherche)
                        ) {

                            afficher = false;
                        }
                    }


                    return afficher;
                }
            );


        afficherCommandes(commandesFiltrees);
    }
);


/*
 * Chargement de la page.
 */
chargerCommandes();