document.addEventListener("DOMContentLoaded", function () {

    const platForm = document.getElementById("platForm");
    const platId = document.getElementById("platId");
    const titrePlat = document.getElementById("titrePlat");
    const menuPlat = document.getElementById("menuPlat");
    const formTitre = document.getElementById("formTitre");
    const btnEnregistrer = document.getElementById("btnEnregistrer");
    const btnAnnuler = document.getElementById("btnAnnulerModification");
    const platsTableBody = document.getElementById("platsTableBody");

    let csrfToken = "";


    // Récupération du jeton CSRF
    fetch("PHP/Check_Session.php", {
        credentials: "same-origin",
        cache: "no-store"
    })
        .then(function (response) {

            if (!response.ok) {
                throw new Error("Erreur lors de la vérification de session.");
            }

            return response.json();
        })
        .then(function (data) {

            if (
                !data.connecte ||
                (!data.admin && !data.employe) ||
                !data.csrf_token
            ) {
                throw new Error("Session employé ou administrateur invalide.");
            }

            csrfToken = data.csrf_token;

            chargerMenus();
            chargerPlats();
        })
        .catch(function (error) {
            console.error(error);
            alert("Impossible de charger la gestion des plats.");
        });


    // Charger les menus disponibles
    function chargerMenus() {

        fetch("PHP/Get_Menus.php", {
            credentials: "same-origin",
            cache: "no-store"
        })
            .then(function (response) {

                if (!response.ok) {
                    throw new Error("Impossible de charger les menus.");
                }

                return response.json();
            })
            .then(function (menus) {

                menuPlat.innerHTML = "";

                const optionDefaut = document.createElement("option");
                optionDefaut.value = "";
                optionDefaut.textContent = "Choisissez un menu";

                menuPlat.appendChild(optionDefaut);

                menus.forEach(function (menu) {

                    const option = document.createElement("option");

                    option.value = menu.menu_id;
                    option.textContent = menu.titre;

                    menuPlat.appendChild(option);
                });
            })
            .catch(function (error) {
                console.error(error);
            });
    }


    // Charger les plats
    function chargerPlats() {

        fetch("PHP/AdminPlats.php", {
            credentials: "same-origin",
            cache: "no-store"
        })
            .then(function (response) {

                if (!response.ok) {
                    throw new Error("Impossible de charger les plats.");
                }

                return response.json();
            })
            .then(function (plats) {

                platsTableBody.innerHTML = "";

                if (plats.length === 0) {

                    const ligne = document.createElement("tr");
                    const cellule = document.createElement("td");

                    cellule.colSpan = 3;
                    cellule.textContent = "Aucun plat enregistré.";

                    ligne.appendChild(cellule);
                    platsTableBody.appendChild(ligne);

                    return;
                }

                plats.forEach(function (plat) {

                    const ligne = document.createElement("tr");


                    // Nom du plat
                    const celluleTitre = document.createElement("td");
                    celluleTitre.textContent = plat.titre_plat;


                    // Menu associé
                    const celluleMenu = document.createElement("td");

                    if (plat.menu_titre) {
                        celluleMenu.textContent = plat.menu_titre;
                    } else {
                        celluleMenu.textContent = "Aucun menu";
                    }


                    // Actions
                    const celluleActions = document.createElement("td");


                    // Modifier
                    const btnModifier = document.createElement("button");

                    btnModifier.type = "button";
                    btnModifier.className = "btn-small";
                    btnModifier.textContent = "Modifier";

                    btnModifier.addEventListener("click", function () {

                        platId.value = plat.plat_id;
                        titrePlat.value = plat.titre_plat;

                        if (plat.menu_id !== null) {
                            menuPlat.value = String(plat.menu_id);
                        } else {
                            menuPlat.value = "";
                        }

                        formTitre.textContent = "Modifier le plat";
                        btnEnregistrer.textContent = "Enregistrer les modifications";
                        btnAnnuler.hidden = false;

                        titrePlat.focus();
                    });


                    // Supprimer
                    const btnSupprimer = document.createElement("button");

                    btnSupprimer.type = "button";
                    btnSupprimer.className = "btn-small";
                    btnSupprimer.textContent = "Supprimer";

                    btnSupprimer.addEventListener("click", function () {

                        const confirmation = confirm(
                            'Supprimer le plat "' + plat.titre_plat + '" ?'
                        );

                        if (!confirmation) {
                            return;
                        }

                        supprimerPlat(plat.plat_id);
                    });


                    celluleActions.appendChild(btnModifier);
                    celluleActions.appendChild(btnSupprimer);

                    ligne.appendChild(celluleTitre);
                    ligne.appendChild(celluleMenu);
                    ligne.appendChild(celluleActions);

                    platsTableBody.appendChild(ligne);
                });
            })
            .catch(function (error) {

                console.error(error);

                platsTableBody.innerHTML = "";

                const ligne = document.createElement("tr");
                const cellule = document.createElement("td");

                cellule.colSpan = 3;
                cellule.textContent = "Erreur lors du chargement des plats.";

                ligne.appendChild(cellule);
                platsTableBody.appendChild(ligne);
            });
    }


    // Ajouter ou modifier un plat
    platForm.addEventListener("submit", function (event) {

        event.preventDefault();

        const titre = titrePlat.value.trim();
        const menuId = menuPlat.value;

        if (titre === "") {
            alert("Veuillez saisir le nom du plat.");
            return;
        }

        if (menuId === "") {
            alert("Veuillez choisir un menu.");
            return;
        }

        const id = platId.value;

        let url = "PHP/AjouterPlat.php";

        if (id !== "") {
            url = "PHP/AdminModifierPlat.php";
        }

        const donnees = new FormData();

        donnees.append("titre_plat", titre);
        donnees.append("menu_id", menuId);
        donnees.append("csrf_token", csrfToken);

        if (id !== "") {
            donnees.append("plat_id", id);
        }


        fetch(url, {
            method: "POST",
            body: donnees,
            credentials: "same-origin"
        })
            .then(function (response) {

                return response.json().then(function (data) {

                    return {
                        ok: response.ok,
                        data: data
                    };
                });
            })
            .then(function (resultat) {

                if (!resultat.ok || !resultat.data.success) {

                    throw new Error(
                        resultat.data.message || "Une erreur est survenue."
                    );
                }

                alert(resultat.data.message);

                reinitialiserFormulaire();
                chargerPlats();
            })
            .catch(function (error) {

                console.error(error);
                alert(error.message);
            });
    });


    // Supprimer un plat
    function supprimerPlat(id) {

        const donnees = new FormData();

        donnees.append("plat_id", id);
        donnees.append("csrf_token", csrfToken);

        fetch("PHP/AdminSupprimerPlat.php", {
            method: "POST",
            body: donnees,
            credentials: "same-origin"
        })
            .then(function (response) {

                return response.json().then(function (data) {

                    return {
                        ok: response.ok,
                        data: data
                    };
                });
            })
            .then(function (resultat) {

                if (!resultat.ok || !resultat.data.success) {

                    throw new Error(
                        resultat.data.message ||
                        "Impossible de supprimer le plat."
                    );
                }

                alert(resultat.data.message);

                reinitialiserFormulaire();
                chargerPlats();
            })
            .catch(function (error) {

                console.error(error);
                alert(error.message);
            });
    }


    // Annuler une modification
    btnAnnuler.addEventListener("click", function () {
        reinitialiserFormulaire();
    });


    // Réinitialiser le formulaire
    function reinitialiserFormulaire() {

        platForm.reset();

        platId.value = "";

        formTitre.textContent = "Ajouter un plat";
        btnEnregistrer.textContent = "Ajouter le plat";
        btnAnnuler.hidden = true;
    }

});