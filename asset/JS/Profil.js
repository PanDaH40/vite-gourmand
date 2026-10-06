document.addEventListener("DOMContentLoaded", () => {

    const profilForm = document.getElementById("profilForm");
    const passwordForm = document.getElementById("passwordForm");

    const messageProfil = document.getElementById("messageProfil");
    const messagePassword = document.getElementById("messagePassword");

    const prenom = document.getElementById("prenom");
    const email = document.getElementById("email");
    const telephone = document.getElementById("telephone");
    const adressePostale = document.getElementById("adresse_postale");
    const ville = document.getElementById("ville");
    const pays = document.getElementById("pays");


    /*
    |--------------------------------------------------------------------------
    | Chargement du profil
    |--------------------------------------------------------------------------
    */

    async function chargerProfil() {

        try {

            const response = await fetch(
                "PHP/Profil.php",
                {
                    credentials: "same-origin"
                }
            );

            const data = await response.json();


            if (!response.ok || !data.success) {

                throw new Error(
                    data.error ||
                    "Impossible de charger le profil."
                );
            }


            const utilisateur = data.utilisateur;

            prenom.value = utilisateur.prenom || "";
            email.value = utilisateur.email || "";
            telephone.value = utilisateur.telephone || "";
            adressePostale.value = utilisateur.adresse_postale || "";
            ville.value = utilisateur.ville || "";
            pays.value = utilisateur.pays || "";


        } catch (error) {

            console.error(error);

            messageProfil.textContent = error.message;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Modification des informations personnelles
    |--------------------------------------------------------------------------
    */

    profilForm.addEventListener("submit", async event => {

        event.preventDefault();

        messageProfil.textContent =
            "Enregistrement en cours...";


        const donnees = {

            prenom: prenom.value.trim(),

            email: email.value.trim(),

            telephone: telephone.value.trim(),

            adresse_postale: adressePostale.value.trim(),

            ville: ville.value.trim(),

            pays: pays.value.trim()
        };


        try {

            const response = await fetch(
                "PHP/ModifierProfil.php",
                {
                    method: "POST",

                    credentials: "same-origin",

                    headers: {
                        "Content-Type": "application/json"
                    },

                    body: JSON.stringify(donnees)
                }
            );


            const data = await response.json();


            if (!response.ok || !data.success) {

                throw new Error(
                    data.error ||
                    "Impossible de modifier le profil."
                );
            }


            messageProfil.textContent =
                "Profil modifié avec succès.";


            await chargerProfil();


        } catch (error) {

            console.error(error);

            messageProfil.textContent = error.message;
        }
    });


    /*
    |--------------------------------------------------------------------------
    | Mot de passe
    |--------------------------------------------------------------------------
    */

    passwordForm.addEventListener("submit", async event => {

    event.preventDefault();

    const currentPassword =
        document.getElementById("current-password").value;

    const newPassword =
        document.getElementById("new-password").value;

    const confirmPassword =
        document.getElementById("confirm-password").value;


    if (newPassword !== confirmPassword) {

        messagePassword.textContent =
            "Les deux nouveaux mots de passe ne correspondent pas.";

        return;
    }


    if (newPassword.length < 8) {

        messagePassword.textContent =
            "Le nouveau mot de passe doit contenir au moins 8 caractères.";

        return;
    }


    messagePassword.textContent =
        "Modification en cours...";


    try {

        const response = await fetch(
            "PHP/ModifierPassword.php",
            {
                method: "POST",

                credentials: "same-origin",

                headers: {
                    "Content-Type": "application/json"
                },

                body: JSON.stringify({

                    current_password: currentPassword,

                    new_password: newPassword,

                    confirm_password: confirmPassword
                })
            }
        );


        const data = await response.json();


        if (!response.ok || !data.success) {

            throw new Error(
                data.error ||
                "Impossible de modifier le mot de passe."
            );
        }


        messagePassword.textContent =
            "Mot de passe modifié avec succès.";


        passwordForm.reset();


    } catch (error) {

        console.error(error);

        messagePassword.textContent = error.message;
    }

});


    /*
    |--------------------------------------------------------------------------
    | Premier chargement
    |--------------------------------------------------------------------------
    */

    chargerProfil();

});