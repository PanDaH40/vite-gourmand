document.addEventListener("DOMContentLoaded", () => {

    const tableBody = document.getElementById("utilisateursTableBody");
    const filterForm = document.getElementById("filterForm");
    const roleFilter = document.getElementById("role");
    const villeFilter = document.getElementById("ville");
    const rechercheFilter = document.getElementById("recherche");
    const message = document.getElementById("messageUtilisateurs");

    let utilisateurs = [];


    /*
    |--------------------------------------------------------------------------
    | Sécurisation simple de l'affichage
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
    | Chargement des utilisateurs
    |--------------------------------------------------------------------------
    */

    async function chargerUtilisateurs() {

        try {

            const response = await fetch("PHP/AdminUtilisateurs.php", {
                credentials: "same-origin"
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(
                    data.error || "Impossible de récupérer les utilisateurs."
                );
            }

            utilisateurs = data.utilisateurs;

            afficherUtilisateurs(utilisateurs);

        } catch (error) {

            console.error(error);

            tableBody.innerHTML = `
                <tr>
                    <td colspan="6">
                        Impossible de charger les utilisateurs.
                    </td>
                </tr>
            `;

            message.textContent = error.message;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Affichage du tableau
    |--------------------------------------------------------------------------
    */

    function afficherUtilisateurs(liste) {

        tableBody.innerHTML = "";

        if (liste.length === 0) {

            tableBody.innerHTML = `
                <tr>
                    <td colspan="6">
                        Aucun utilisateur trouvé.
                    </td>
                </tr>
            `;

            return;
        }


        liste.forEach(utilisateur => {

            /*
             * Un utilisateur sans rôle dans la table de liaison
             * est considéré ici comme un utilisateur standard.
             */

            const role = utilisateur.role || "Utilisateur";

            let badgeClass = "accepted";

            if (role === "Administrateur") {
                badgeClass = "done";
            }


            const ligne = document.createElement("tr");

            ligne.innerHTML = `
                <td>
                    ${escapeHtml(utilisateur.prenom)}
                </td>

                <td>
                    ${escapeHtml(utilisateur.email)}
                </td>

                <td>
                    ${escapeHtml(utilisateur.telephone || "-")}
                </td>

                <td>
                    ${escapeHtml(utilisateur.ville || "-")}
                </td>

                <td>
                    <span class="badge ${badgeClass}">
                        ${escapeHtml(role)}
                    </span>
                </td>

                <td>
                    <div class="table-actions">

                        <button
                            type="button"
                            class="btn-small btn-role"
                            data-id="${utilisateur.utilisateur_id}"
                        >
                            Modifier rôle
                        </button>

                    </div>
                </td>
            `;

            tableBody.appendChild(ligne);
        });
    }


    /*
    |--------------------------------------------------------------------------
    | Filtres
    |--------------------------------------------------------------------------
    */

    filterForm.addEventListener("submit", event => {

        event.preventDefault();

        const roleRecherche = roleFilter.value.toLowerCase().trim();
        const villeRecherche = villeFilter.value.toLowerCase().trim();
        const texteRecherche = rechercheFilter.value.toLowerCase().trim();


        const resultat = utilisateurs.filter(utilisateur => {

            const role = (
                utilisateur.role || "Utilisateur"
            ).toLowerCase();

            const ville = (
                utilisateur.ville || ""
            ).toLowerCase();

            const prenom = (
                utilisateur.prenom || ""
            ).toLowerCase();

            const email = (
                utilisateur.email || ""
            ).toLowerCase();


            const correspondRole =
                roleRecherche === "" ||
                role === roleRecherche;


            const correspondVille =
                villeRecherche === "" ||
                ville.includes(villeRecherche);


            const correspondRecherche =
                texteRecherche === "" ||
                prenom.includes(texteRecherche) ||
                email.includes(texteRecherche);


            return (
                correspondRole &&
                correspondVille &&
                correspondRecherche
            );
        });


        afficherUtilisateurs(resultat);
    });


    /*
    |--------------------------------------------------------------------------
    | Bouton Modifier rôle
    |--------------------------------------------------------------------------
    */

    tableBody.addEventListener("click", event => {

        const bouton = event.target.closest(".btn-role");

        if (!bouton) {
            return;
        }

        const utilisateurId = bouton.dataset.id;

        const utilisateur = utilisateurs.find(
            utilisateur =>
                String(utilisateur.utilisateur_id) ===
                String(utilisateurId)
        );


        if (!utilisateur) {
            return;
        }


        const roleActuel =
            utilisateur.role || "Utilisateur";


        /*
         * Pour l'instant le bouton permet de choisir
         * entre les deux rôles réellement présents
         * dans la base.
         */

        const nouveauRole =
            roleActuel === "Administrateur"
                ? "Utilisateur"
                : "Administrateur";


        const confirmation = confirm(
            `Modifier le rôle de ${utilisateur.prenom} :\n\n` +
            `${roleActuel} → ${nouveauRole} ?`
        );


        if (!confirmation) {
            return;
        }


        modifierRole(
            utilisateur.utilisateur_id,
            nouveauRole
        );
    });


    /*
    |--------------------------------------------------------------------------
    | Modification du rôle
    |--------------------------------------------------------------------------
    */

    async function modifierRole(utilisateurId, role) {

        try {

            message.textContent = "Modification en cours...";


            const response = await fetch(
                "PHP/AdminModifierRole.php",
                {
                    method: "POST",

                    credentials: "same-origin",

                    headers: {
                        "Content-Type": "application/json"
                    },

                    body: JSON.stringify({
                        utilisateur_id: utilisateurId,
                        role: role
                    })
                }
            );


            const data = await response.json();


            if (!response.ok || !data.success) {

                throw new Error(
                    data.error ||
                    "Impossible de modifier le rôle."
                );
            }


            message.textContent =
                "Le rôle a été modifié avec succès.";


            await chargerUtilisateurs();


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

    chargerUtilisateurs();

});