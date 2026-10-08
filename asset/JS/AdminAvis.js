
document.addEventListener("DOMContentLoaded", function () {

    const tableBody =
        document.getElementById("avisTableBody");

    const message =
        document.getElementById("messageAvis");

    const avisAttente =
        document.getElementById("avisAttente");

    const avisAcceptes =
        document.getElementById("avisAcceptes");

    const avisRefuses =
        document.getElementById("avisRefuses");


    /**
     * Protection de l'affichage HTML
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


    /**
     * Chargement des avis
     */
    async function chargerAvis() {

        try {

            const response = await fetch(
                "PHP/AdminAvis.php",
                {
                    credentials: "same-origin"
                }
            );

            const data = await response.json();

            if (!response.ok || !data.success) {

                throw new Error(
                    data.error ||
                    "Impossible de récupérer les avis."
                );
            }

            // Statistiques
            avisAttente.textContent =
                data.statistiques.attente;

            avisAcceptes.textContent =
                data.statistiques.acceptes;

            avisRefuses.textContent =
                data.statistiques.refuses;

            // Tableau
            afficherAvis(data.avis);

        } catch (error) {

            console.error(error);

            message.textContent =
                error.message;

            tableBody.innerHTML = `
                <tr>
                    <td colspan="5">
                        Impossible de charger les avis.
                    </td>
                </tr>
            `;
        }
    }


    /**
     * Affichage
     */
    function afficherAvis(avis) {

        tableBody.innerHTML = "";

        if (avis.length === 0) {

            tableBody.innerHTML = `
                <tr>
                    <td colspan="5">
                        Aucun avis enregistré.
                    </td>
                </tr>
            `;

            return;
        }

        avis.forEach(function (unAvis) {

            const ligne =
                document.createElement("tr");

            const statut =
                String(unAvis.statut).toLowerCase();

            let badgeClass = "pending";

            if (
                statut === "accepté" ||
                statut === "accepte"
            ) {
                badgeClass = "accepted";
            }

            if (
                statut === "refusé" ||
                statut === "refuse"
            ) {
                badgeClass = "late";
            }

            // Boutons uniquement pour les avis en attente
            let actions = "<span>Terminé</span>";

            if (statut === "en attente") {

                actions = `
                    <button
                        type="button"
                        class="btn-small btn-accepter-avis"
                        data-id="${Number(unAvis.avis_id)}"
                    >
                        Accepter
                    </button>

                    <button
                        type="button"
                        class="btn-small btn-danger btn-refuser-avis"
                        data-id="${Number(unAvis.avis_id)}"
                    >
                        Refuser
                    </button>
                `;
            }

            ligne.innerHTML = `
                <td>
                    ${escapeHtml(unAvis.prenom)}
                    <br>
                    <small>
                        ${escapeHtml(unAvis.email)}
                    </small>
                </td>

                <td>
                    ${escapeHtml(unAvis.note)} / 5
                </td>

                <td>
                    ${escapeHtml(unAvis.description)}
                </td>

                <td>
                    <span class="badge ${badgeClass}">
                        ${escapeHtml(unAvis.statut)}
                    </span>
                </td>

                <td>
                    <div class="table-actions">
                        ${actions}
                    </div>
                </td>
            `;

            tableBody.appendChild(ligne);
        });
    }


    /**
     * Accepter / Refuser
     */
    tableBody.addEventListener(
        "click",
        function (event) {

            const accepter =
                event.target.closest(
                    ".btn-accepter-avis"
                );

            const refuser =
                event.target.closest(
                    ".btn-refuser-avis"
                );

            if (accepter) {

                modifierAvis(
                    accepter.dataset.id,
                    "Accepté"
                );
            }

            if (refuser) {

                modifierAvis(
                    refuser.dataset.id,
                    "Refusé"
                );
            }
        }
    );


    /**
     * Modification du statut
     */
    async function modifierAvis(
        avisId,
        nouveauStatut
    ) {

        const confirmation = confirm(
            "Confirmer le statut « " +
            nouveauStatut +
            " » pour cet avis ?"
        );

        if (!confirmation) {
            return;
        }

        message.textContent =
            "Modification en cours...";

        try {

            /**
             * Récupération du jeton CSRF
             */
            const sessionResponse = await fetch(
                "PHP/Check_Session.php",
                {
                    credentials: "same-origin",
                    cache: "no-store"
                }
            );

            const sessionData =
                await sessionResponse.json();

            if (
                !sessionResponse.ok ||
                !sessionData.connecte ||
                (!sessionData.admin && !sessionData.employe) ||
                !sessionData.csrf_token
            ) {
                throw new Error(
                    "Session administrateur invalide."
                );
            }

            /**
             * Modification de l'avis
             */
            const response = await fetch(
                "PHP/AdminModifierAvis.php",
                {
                    method: "POST",

                    credentials: "same-origin",

                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-Token": sessionData.csrf_token
                    },

                    body: JSON.stringify({
                        avis_id: avisId,
                        statut: nouveauStatut
                    })
                }
            );

            const data =
                await response.json();

            if (!response.ok || !data.success) {

                throw new Error(
                    data.error ||
                    "Impossible de modifier l'avis."
                );
            }

            message.textContent =
                "Statut de l'avis modifié.";

            await chargerAvis();

        } catch (error) {

            console.error(error);

            message.textContent =
                error.message;
        }
    }


    /**
     * Premier chargement
     */
    chargerAvis();

});
