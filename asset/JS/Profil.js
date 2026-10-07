
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

        messageProfil.textContent = "Enregistrement en cours...";

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
    | Modification du mot de passe
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

        // Vérifier les champs
        if (
            currentPassword === "" ||
            newPassword === "" ||
            confirmPassword === ""
        ) {
            messagePassword.textContent =
                "Tous les champs sont obligatoires.";
            return;
        }

        // Vérifier la confirmation
        if (newPassword !== confirmPassword) {
            messagePassword.textContent =
                "Les deux nouveaux mots de passe ne correspondent pas.";
            return;
        }

        // Même règle que l'inscription
        const motDePasseValide =
            newPassword.length >= 10 &&
            /[A-Z]/.test(newPassword) &&
            /[a-z]/.test(newPassword) &&
            /[0-9]/.test(newPassword) &&
            /[^A-Za-z0-9]/.test(newPassword);

        if (!motDePasseValide) {
            messagePassword.textContent =
                "Le mot de passe doit contenir au moins 10 caractères, " +
                "une majuscule, une minuscule, un chiffre " +
                "et un caractère spécial.";
            return;
        }

        messagePassword.textContent =
            "Modification en cours...";

        try {

            // Récupérer le jeton CSRF
            const sessionResponse = await fetch(
                "PHP/Check_Session.php",
                {
                    credentials: "same-origin",
                    cache: "no-store"
                }
            );

            if (!sessionResponse.ok) {
                throw new Error(
                    "Impossible de vérifier la session."
                );
            }

            const session = await sessionResponse.json();

            if (!session.connecte || !session.csrf_token) {
                throw new Error(
                    "Session expirée ou jeton de sécurité indisponible."
                );
            }

            // Envoyer les données au serveur
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
                        confirm_password: confirmPassword,
                        csrf_token: session.csrf_token
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
