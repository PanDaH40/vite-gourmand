document.addEventListener("DOMContentLoaded", function () {

    const reviewsGrid =
        document.getElementById("reviewsGrid");


    /*
    |--------------------------------------------------------------------------
    | Protection du contenu provenant de la BDD
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
    | Chargement des avis
    |--------------------------------------------------------------------------
    */

    async function chargerAvis() {

        try {

            const response =
                await fetch("PHP/AvisPublic.php");


            const data =
                await response.json();


            if (!response.ok || !data.success) {

                throw new Error(
                    data.error ||
                    "Impossible de charger les avis."
                );
            }


            afficherAvis(data.avis);


        } catch (error) {

            console.error(error);

            reviewsGrid.innerHTML =
                "<p>Les avis sont momentanément indisponibles.</p>";
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Affichage
    |--------------------------------------------------------------------------
    */

    function afficherAvis(avis) {

        reviewsGrid.innerHTML = "";


        if (avis.length === 0) {

            reviewsGrid.innerHTML =
                "<p>Aucun avis publié pour le moment.</p>";

            return;
        }


        avis.forEach(function (unAvis) {

            const article =
                document.createElement("article");


            article.className = "review";


            article.innerHTML = `
                <p class="review-text">
                    “${escapeHtml(unAvis.description)}”
                </p>

                <p class="review-meta">
                    <strong>
                        ${escapeHtml(unAvis.prenom)}
                    </strong>
                    — Note :
                    ${escapeHtml(unAvis.note)}/5
                </p>
            `;


            reviewsGrid.appendChild(article);
        });
    }


    chargerAvis();

});