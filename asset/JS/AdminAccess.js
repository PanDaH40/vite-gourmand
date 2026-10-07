
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
        if (!data.connecte || !data.admin) {
            alert("Accès refusé.");
            window.location.replace("PagePrincipale.html");
        }
    })
    .catch(function () {
        window.location.replace("PagePrincipale.html");
    });
