document.addEventListener("DOMContentLoaded", () => {

    const tableBody = document.getElementById("materielTableBody");
    const message = document.getElementById("messageMateriel");

    const nombrePrets = document.getElementById("nombrePrets");
    const nombreAttente = document.getElementById("nombreAttente");
    const nombreRestitues = document.getElementById("nombreRestitues");


    /*
    |--------------------------------------------------------------------------
    | Protection de l'affichage HTML
    |--------------------------------------------------------------------------
    */

    function escapeHtml(value) {

        if (value === null || value === undefined) {
            return "";
        }

        return String(value)
            .replaceAll("&", "&amp;")
            .replaceAll("<", "&lt;")
            .replaceAll(">", "&gt;")
            .replaceAll('"', "&quot;")
            .replaceAll("'", "&#039;");
    }


    /*
    |--------------------------------------------------------------------------
    | Formatage des dates
    |--------------------------------------------------------------------------
    */

    function formaterDate(date) {

        if (!date) {
            return "-";
        }

        const morceaux = date.split("-");

        if (morceaux.length !== 3) {
            return date;
        }

        return `${morceaux[2]}/${morceaux[1]}/${morceaux[0]}`;
    }


    /*
    |--------------------------------------------------------------------------
    | Chargement du matériel
    |--------------------------------------------------------------------------
    */

    async function chargerMateriel() {

        try {

            const response = await fetch(
                "PHP/AdminMateriel.php",
                {
                    credentials: "same-origin"
                }
            );

            const data = await response.json();


            if (!response.ok || !data.success) {

                throw new Error(
                    data.error ||
                    "Impossible de récupérer les prêts de matériel."
                );
            }


            /*
             * Mise à jour des statistiques
             */

            nombrePrets.textContent =
                data.statistiques.prets;

            nombreAttente.textContent =
                data.statistiques.attente;

            nombreRestitues.textContent =
                data.statistiques.restitues;


            /*
             * Affichage du tableau
             */

            afficherMateriel(data.materiels);


        } catch (error) {

            console.error(error);

            message.textContent = error.message;

            tableBody.innerHTML = `
                <tr>
                    <td colspan="7">
                        Impossible de charger les prêts de matériel.
                    </td>
                </tr>
            `;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Affichage du tableau
    |--------------------------------------------------------------------------
    */

    function afficherMateriel(materiels) {

        tableBody.innerHTML = "";


        if (materiels.length === 0) {

            tableBody.innerHTML = `
                <tr>
                    <td colspan="7">
                        Aucun prêt de matériel enregistré.
                    </td>
                </tr>
            `;

            return;
        }


        materiels.forEach(materiel => {

            const restitue =
                Number(materiel.restitution_materiel) === 1;


            const ligne = document.createElement("tr");


            ligne.innerHTML = `

                <td>
                    ${escapeHtml(materiel.numero_commande)}
                </td>

                <td>
                    ${escapeHtml(materiel.prenom)}
                </td>

                <td>
                    ${escapeHtml(materiel.telephone || "-")}
                </td>

                <td>
                    ${escapeHtml(
                        formaterDate(materiel.date_prestation)
                    )}
                </td>

                <td>
                    <span class="badge accepted">
                        Oui
                    </span>
                </td>

                <td>

                    ${
                        restitue

                        ? `
                            <span class="badge accepted">
                                Restitué
                            </span>
                          `

                        : `
                            <span class="badge pending">
                                En attente
                            </span>
                          `
                    }

                </td>

                <td>

                    ${
                        restitue

                        ? `
                            <span>
                                Terminé
                            </span>
                          `

                        : `
                            <button
                                type="button"
                                class="btn-small btn-retour"
                                data-commande="${escapeHtml(
                                    materiel.numero_commande
                                )}"
                            >
                                Retour effectué
                            </button>
                          `
                    }

                </td>
            `;


            tableBody.appendChild(ligne);
        });
    }


    /*
    |--------------------------------------------------------------------------
    | Clic sur "Retour effectué"
    |--------------------------------------------------------------------------
    */

    tableBody.addEventListener("click", event => {

        const bouton = event.target.closest(".btn-retour");


        if (!bouton) {
            return;
        }


        const numeroCommande =
            bouton.dataset.commande;


        const confirmation = confirm(
            `Confirmer la restitution du matériel pour la commande ${numeroCommande} ?`
        );


        if (!confirmation) {
            return;
        }


        enregistrerRestitution(numeroCommande);
    });


    /*
    |--------------------------------------------------------------------------
    | Enregistrement de la restitution
    |--------------------------------------------------------------------------
    */

    async function enregistrerRestitution(numeroCommande) {

        try {

            message.textContent =
                "Enregistrement de la restitution...";


            const response = await fetch(
                "PHP/AdminRestitutionMateriel.php",
                {
                    method: "POST",

                    credentials: "same-origin",

                    headers: {
                        "Content-Type": "application/json"
                    },

                    body: JSON.stringify({
                        numero_commande: numeroCommande
                    })
                }
            );


            const data = await response.json();


            if (!response.ok || !data.success) {

                throw new Error(
                    data.error ||
                    "Impossible d'enregistrer la restitution."
                );
            }


            message.textContent =
                "La restitution du matériel a été enregistrée.";


            await chargerMateriel();


        } catch (error) {

            console.error(error);

            message.textContent = error.message;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Premier chargement
    |--------------------------------------------------------------------------
    */

    chargerMateriel();

});