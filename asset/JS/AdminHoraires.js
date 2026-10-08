document.addEventListener("DOMContentLoaded", function () {

    const horaireForm = document.getElementById("horaireForm");
    const horaireId = document.getElementById("horaireId");
    const jour = document.getElementById("jour");
    const heureOuverture = document.getElementById("heureOuverture");
    const heureFermeture = document.getElementById("heureFermeture");
    const formTitre = document.getElementById("formTitre");
    const btnEnregistrer = document.getElementById("btnEnregistrer");
    const btnAnnuler = document.getElementById("btnAnnulerModification");
    const horairesTableBody = document.getElementById("horairesTableBody");

    let csrfToken = "";


    // Vérification de la session et récupération du jeton CSRF
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

            chargerHoraires();
        })
        .catch(function (error) {

            console.error(error);
            alert("Impossible de charger la gestion des horaires.");
        });


    // Charger les horaires
    function chargerHoraires() {

        fetch("PHP/AdminHoraires.php", {
            credentials: "same-origin",
            cache: "no-store"
        })
            .then(function (response) {

                if (!response.ok) {
                    throw new Error("Impossible de charger les horaires.");
                }

                return response.json();
            })
            .then(function (horaires) {

                horairesTableBody.innerHTML = "";

                if (horaires.length === 0) {

                    const ligne = document.createElement("tr");
                    const cellule = document.createElement("td");

                    cellule.colSpan = 4;
                    cellule.textContent = "Aucun horaire enregistré.";

                    ligne.appendChild(cellule);
                    horairesTableBody.appendChild(ligne);

                    return;
                }

                horaires.forEach(function (horaire) {

                    const ligne = document.createElement("tr");

                    const celluleJour = document.createElement("td");
                    celluleJour.textContent = horaire.jour;

                    const celluleOuverture = document.createElement("td");
                    celluleOuverture.textContent = horaire.heure_ouverture;

                    const celluleFermeture = document.createElement("td");
                    celluleFermeture.textContent = horaire.heure_fermeture;

                    const celluleActions = document.createElement("td");


                    // Modifier
                    const btnModifier = document.createElement("button");

                    btnModifier.type = "button";
                    btnModifier.className = "btn-small";
                    btnModifier.textContent = "Modifier";

                    btnModifier.addEventListener("click", function () {

                        horaireId.value = horaire.horaire_id;
                        jour.value = horaire.jour;

                        heureOuverture.value =
                            horaire.heure_ouverture.substring(0, 5);

                        heureFermeture.value =
                            horaire.heure_fermeture.substring(0, 5);

                        formTitre.textContent = "Modifier l'horaire";
                        btnEnregistrer.textContent =
                            "Enregistrer les modifications";

                        btnAnnuler.hidden = false;

                        jour.focus();
                    });


                    // Supprimer
                    const btnSupprimer = document.createElement("button");

                    btnSupprimer.type = "button";
                    btnSupprimer.className = "btn-small";
                    btnSupprimer.textContent = "Supprimer";

                    btnSupprimer.addEventListener("click", function () {

                        const confirmation = confirm(
                            "Supprimer l'horaire du " + horaire.jour + " ?"
                        );

                        if (!confirmation) {
                            return;
                        }

                        supprimerHoraire(horaire.horaire_id);
                    });


                    celluleActions.appendChild(btnModifier);
                    celluleActions.appendChild(btnSupprimer);

                    ligne.appendChild(celluleJour);
                    ligne.appendChild(celluleOuverture);
                    ligne.appendChild(celluleFermeture);
                    ligne.appendChild(celluleActions);

                    horairesTableBody.appendChild(ligne);
                });
            })
            .catch(function (error) {

                console.error(error);

                horairesTableBody.innerHTML = "";

                const ligne = document.createElement("tr");
                const cellule = document.createElement("td");

                cellule.colSpan = 4;
                cellule.textContent =
                    "Erreur lors du chargement des horaires.";

                ligne.appendChild(cellule);
                horairesTableBody.appendChild(ligne);
            });
    }


    // Ajouter ou modifier
    horaireForm.addEventListener("submit", function (event) {

        event.preventDefault();

        if (
            jour.value === "" ||
            heureOuverture.value === "" ||
            heureFermeture.value === ""
        ) {
            alert("Veuillez compléter tous les champs.");
            return;
        }

        const id = horaireId.value;

        let url = "PHP/AjouterHoraire.php";

        if (id !== "") {
            url = "PHP/AdminModifierHoraire.php";
        }

        const donnees = new FormData();

        donnees.append("jour", jour.value);
        donnees.append("heure_ouverture", heureOuverture.value);
        donnees.append("heure_fermeture", heureFermeture.value);
        donnees.append("csrf_token", csrfToken);

        if (id !== "") {
            donnees.append("horaire_id", id);
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
                chargerHoraires();
            })
            .catch(function (error) {

                console.error(error);
                alert(error.message);
            });
    });


    // Supprimer
    function supprimerHoraire(id) {

        const donnees = new FormData();

        donnees.append("horaire_id", id);
        donnees.append("csrf_token", csrfToken);

        fetch("PHP/AdminSupprimerHoraire.php", {
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
                        "Impossible de supprimer l'horaire."
                    );
                }

                alert(resultat.data.message);

                reinitialiserFormulaire();
                chargerHoraires();
            })
            .catch(function (error) {

                console.error(error);
                alert(error.message);
            });
    }


    // Annuler la modification
    btnAnnuler.addEventListener("click", function () {
        reinitialiserFormulaire();
    });


    function reinitialiserFormulaire() {

        horaireForm.reset();

        horaireId.value = "";

        formTitre.textContent = "Ajouter un horaire";
        btnEnregistrer.textContent = "Ajouter l'horaire";
        btnAnnuler.hidden = true;
    }

});