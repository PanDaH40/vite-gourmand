fetch("PHP/Check_Session.php", {
    credentials: "same-origin",
    cache: "no-store"
})
    .then(function (response) {
        if (!response.ok) {
            throw new Error("Erreur de vérification");
        }

        return response.json();
    })
    .then(function (data) {

        // L'espace employé est accessible aux employés
        // ainsi qu'aux administrateurs.
        if (!data.connecte || (!data.employe && !data.admin)) {
            alert("Accès refusé.");
            window.location.replace("PagePrincipale.html");
        }
    })
    .catch(function () {
        window.location.replace("PagePrincipale.html");
    });