document.addEventListener("DOMContentLoaded", function () {

    const horairesContainer =
        document.getElementById("horairesFooter");

    if (!horairesContainer) {
        return;
    }


    async function chargerHoraires() {

        try {

            const response = await fetch(
                "PHP/Horaires.php"
            );

            const data = await response.json();


            if (!response.ok || !data.success) {

                throw new Error(
                    data.error ||
                    "Impossible de charger les horaires."
                );
            }


            afficherHoraires(data.horaires);


        } catch (error) {

            console.error(error);

            horairesContainer.textContent =
                "Horaires momentanément indisponibles.";
        }
    }


    function afficherHoraires(horaires) {

        horairesContainer.innerHTML = "";


        if (horaires.length === 0) {

            horairesContainer.textContent =
                "Horaires non renseignés.";

            return;
        }


        horaires.forEach(function (horaire) {

            const ligne =
                document.createElement("p");


            const ouverture =
                horaire.heure_ouverture.replace(
                    ":",
                    "h"
                );


            const fermeture =
                horaire.heure_fermeture.replace(
                    ":",
                    "h"
                );


            ligne.textContent =
                horaire.jour +
                " : " +
                ouverture +
                " - " +
                fermeture;


            horairesContainer.appendChild(ligne);
        });
    }


    chargerHoraires();

});