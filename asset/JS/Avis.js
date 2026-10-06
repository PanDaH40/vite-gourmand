document.addEventListener("DOMContentLoaded", function () {

    const form =
        document.getElementById("avisForm");

    const note =
        document.getElementById("note");

    const description =
        document.getElementById("description");

    const message =
        document.getElementById("messageAvis");

    const commandeAvis =
        document.getElementById("commandeAvis");


    /*
    |--------------------------------------------------------------------------
    | Numéro de commande transmis depuis Mes commandes
    |--------------------------------------------------------------------------
    */

    const params =
        new URLSearchParams(window.location.search);

    const numeroCommande =
        params.get("commande");


    if (!numeroCommande) {

        message.textContent =
            "Aucune commande sélectionnée.";

        form.style.display = "none";

        return;
    }


    commandeAvis.textContent =
        "Commande : " + numeroCommande;


    /*
    |--------------------------------------------------------------------------
    | Envoi de l'avis
    |--------------------------------------------------------------------------
    */

    form.addEventListener(
        "submit",
        async function (event) {

            event.preventDefault();


            if (
                note.value === "" ||
                description.value.trim() === ""
            ) {

                message.textContent =
                    "Veuillez renseigner une note et un commentaire.";

                return;
            }


            message.textContent =
                "Envoi de votre avis...";


            try {

                const response = await fetch(
                    "PHP/AjouterAvis.php",
                    {
                        method: "POST",

                        credentials: "same-origin",

                        headers: {
                            "Content-Type": "application/json"
                        },

                        body: JSON.stringify({

                            numero_commande:
                                numeroCommande,

                            note:
                                note.value,

                            description:
                                description.value.trim()
                        })
                    }
                );


                const data =
                    await response.json();


                if (
                    !response.ok ||
                    !data.success
                ) {

                    throw new Error(
                        data.error ||
                        "Impossible d'enregistrer l'avis."
                    );
                }


                message.textContent =
                    "Votre avis a été envoyé et sera vérifié avant publication.";

                form.reset();


                setTimeout(function () {

                    window.location.href =
                        "MesCommandes.html";

                }, 1500);


            } catch (error) {

                console.error(error);

                message.textContent =
                    error.message;
            }

        }
    );

});