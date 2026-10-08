const menusTableBody = document.getElementById("menusTableBody");

const menuForm = document.getElementById("menuForm");
const menuId = document.getElementById("menuId");

const titre = document.getElementById("titre");
const prix = document.getElementById("prix");
const stock = document.getElementById("stock");
const minimum = document.getElementById("minimum");
const regime = document.getElementById("regime");
const description = document.getElementById("description");

const formTitre = document.getElementById("formTitre");
const btnEnregistrer = document.getElementById("btnEnregistrer");
const btnAnnuler = document.getElementById("btnAnnulerModification");

let menusActuels = [];
let csrfToken = "";


/**
 * Récupère le jeton CSRF de la session
 * employé ou administrateur.
 */
async function chargerJetonCSRF() {

    const response = await fetch("PHP/Check_Session.php", {
        credentials: "same-origin",
        cache: "no-store",
    });

    if (!response.ok) {
        throw new Error("Impossible de vérifier la session.");
    }

    const data = await response.json();

    if (
        !data.connecte ||
        (!data.admin && !data.employe) ||
        !data.csrf_token
    ) {
        throw new Error(
            "Session employé ou administrateur invalide."
        );
    }

    csrfToken = data.csrf_token;

    // Prépare le jeton pour le formulaire HTML d'ajout.
    let champCSRF = menuForm.querySelector(
        'input[name="csrf_token"]'
    );

    if (!champCSRF) {
        champCSRF = document.createElement("input");
        champCSRF.type = "hidden";
        champCSRF.name = "csrf_token";
        menuForm.appendChild(champCSRF);
    }

    champCSRF.value = csrfToken;
}


/**
 * Charge les menus depuis MySQL.
 */
function chargerMenus() {

    fetch("PHP/Get_Menus.php")

        .then(function (response) {

            if (!response.ok) {
                throw new Error(
                    "Impossible de charger les menus."
                );
            }

            return response.json();
        })

        .then(function (menus) {

            menusActuels = menus;

            afficherMenus(menus);
        })

        .catch(function (error) {

            console.error(error);

            menusTableBody.innerHTML = "";

            const tr = document.createElement("tr");
            const td = document.createElement("td");

            td.colSpan = 6;
            td.textContent =
                "Impossible de charger les menus.";

            tr.appendChild(td);
            menusTableBody.appendChild(tr);
        });
}


/**
 * Affiche les menus sans injecter de HTML
 * provenant de la base de données.
 */
function afficherMenus(menus) {

    menusTableBody.innerHTML = "";

    if (menus.length === 0) {

        const tr = document.createElement("tr");
        const td = document.createElement("td");

        td.colSpan = 6;
        td.textContent = "Aucun menu enregistré.";

        tr.appendChild(td);
        menusTableBody.appendChild(tr);

        return;
    }

    menus.forEach(function (menu) {

        const tr = document.createElement("tr");

        const valeurs = [
            menu.titre,
            parseFloat(menu.prix_par_personne).toFixed(2) + " €",
            menu.nombre_personne_minimum,
            menu.quantite_restante,
            menu.regime,
        ];

        valeurs.forEach(function (valeur) {

            const td = document.createElement("td");

            td.textContent = valeur ?? "";

            tr.appendChild(td);
        });

        const tdActions = document.createElement("td");

        const divActions =
            document.createElement("div");

        divActions.className = "table-actions";


        const btnModifier =
            document.createElement("button");

        btnModifier.type = "button";
        btnModifier.className =
            "btn-small btn-modifier";

        btnModifier.dataset.id =
            menu.menu_id;

        btnModifier.textContent =
            "Modifier";


        const btnSupprimer =
            document.createElement("button");

        btnSupprimer.type = "button";
        btnSupprimer.className =
            "btn-small btn-danger btn-supprimer";

        btnSupprimer.dataset.id =
            menu.menu_id;

        btnSupprimer.textContent =
            "Supprimer";


        divActions.appendChild(btnModifier);
        divActions.appendChild(btnSupprimer);

        tdActions.appendChild(divActions);

        tr.appendChild(tdActions);

        menusTableBody.appendChild(tr);
    });
}


/**
 * Clic sur Modifier / Supprimer.
 */
menusTableBody.addEventListener(
    "click",
    async function (event) {

        const bouton =
            event.target.closest("button");

        if (!bouton) {
            return;
        }

        const id =
            parseInt(bouton.dataset.id, 10);

        const menu =
            menusActuels.find(function (element) {

                return (
                    parseInt(
                        element.menu_id,
                        10
                    ) === id
                );
            });

        if (!menu) {
            return;
        }


        /**
         * MODIFIER
         */
        if (
            bouton.classList.contains(
                "btn-modifier"
            )
        ) {

            menuId.value =
                menu.menu_id;

            titre.value =
                menu.titre;

            prix.value =
                menu.prix_par_personne;

            stock.value =
                menu.quantite_restante;

            minimum.value =
                menu.nombre_personne_minimum;

            regime.value =
                menu.regime;

            description.value =
                menu.description;

            formTitre.textContent =
                "Modifier le menu";

            btnEnregistrer.textContent =
                "Enregistrer les modifications";

            btnAnnuler.hidden = false;

            menuForm.scrollIntoView({
                behavior: "smooth",
            });

            return;
        }


        /**
         * SUPPRIMER
         */
        if (
            bouton.classList.contains(
                "btn-supprimer"
            )
        ) {

            const confirmation = confirm(
                'Voulez-vous vraiment supprimer le menu "' +
                menu.titre +
                '" ?'
            );

            if (!confirmation) {
                return;
            }

            try {

                if (!csrfToken) {
                    throw new Error(
                        "Jeton de sécurité indisponible."
                    );
                }

                const response =
                    await fetch(
                        "PHP/AdminSupprimerMenu.php",
                        {
                            method: "POST",

                            credentials:
                                "same-origin",

                            headers: {
                                "Content-Type":
                                    "application/json",

                                "X-CSRF-Token":
                                    csrfToken,
                            },

                            body: JSON.stringify({
                                menu_id:
                                    menu.menu_id,
                            }),
                        }
                    );

                const texte =
                    await response.text();

                let data;

                try {

                    data =
                        JSON.parse(texte);

                } catch (error) {

                    console.error(
                        "Réponse PHP :",
                        texte
                    );

                    throw new Error(
                        "Le serveur n'a pas renvoyé une réponse valide."
                    );
                }

                if (
                    !response.ok ||
                    !data.success
                ) {

                    throw new Error(
                        data.message ||
                        "Impossible de supprimer le menu."
                    );
                }

                alert(data.message);

                chargerMenus();

            } catch (error) {

                console.error(error);

                alert(error.message);
            }
        }
    }
);


/**
 * Envoi du formulaire.
 */
menuForm.addEventListener(
    "submit",
    function (event) {

        if (!csrfToken) {

            event.preventDefault();

            alert(
                "Jeton de sécurité indisponible. Rechargez la page."
            );

            return;
        }


        /**
         * AJOUT :
         * Le formulaire HTML est envoyé normalement
         * vers AjouterMenu.php.
         */
        if (menuId.value === "") {
            return;
        }


        /**
         * MODIFICATION :
         * Envoi JSON vers AdminModifierMenu.php.
         */
        event.preventDefault();

        const donnees = {

            menu_id:
                parseInt(menuId.value, 10),

            titre:
                titre.value.trim(),

            prix:
                parseFloat(prix.value),

            stock:
                parseInt(stock.value, 10),

            minimum:
                parseInt(minimum.value, 10),

            regime:
                regime.value,

            description:
                description.value.trim(),
        };


        fetch(
            "PHP/AdminModifierMenu.php",
            {
                method: "POST",

                credentials:
                    "same-origin",

                headers: {

                    "Content-Type":
                        "application/json",

                    "X-CSRF-Token":
                        csrfToken,
                },

                body:
                    JSON.stringify(
                        donnees
                    ),
            }
        )

            .then(function (response) {

                return response
                    .text()
                    .then(function (texte) {

                        let data;

                        try {

                            data =
                                JSON.parse(
                                    texte
                                );

                        } catch (error) {

                            console.error(
                                "Réponse PHP reçue :",
                                texte
                            );

                            throw new Error(
                                "Le serveur n'a pas renvoyé une réponse JSON valide."
                            );
                        }

                        if (!response.ok) {

                            throw new Error(
                                data.message ||
                                "Erreur lors de la modification."
                            );
                        }

                        return data;
                    });
            })

            .then(function (data) {

                if (!data.success) {

                    alert(data.message);

                    return;
                }

                alert(
                    "Menu modifié avec succès."
                );

                menuForm.reset();

                menuId.value = "";

                formTitre.textContent =
                    "Ajouter un menu";

                btnEnregistrer.textContent =
                    "Ajouter le menu";

                btnAnnuler.hidden = true;

                chargerMenus();
            })

            .catch(function (error) {

                console.error(error);

                alert(error.message);
            });
    }
);


/**
 * Annuler la modification.
 */
btnAnnuler.addEventListener(
    "click",
    function () {

        menuForm.reset();

        menuId.value = "";

        formTitre.textContent =
            "Ajouter un menu";

        btnEnregistrer.textContent =
            "Ajouter le menu";

        btnAnnuler.hidden = true;
    }
);


/**
 * Chargement initial.
 */
btnEnregistrer.disabled = true;

chargerJetonCSRF()

    .then(function () {

        btnEnregistrer.disabled = false;
    })

    .catch(function (error) {

        console.error(error);

        alert(error.message);
    });

chargerMenus();