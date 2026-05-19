fetch("php/check_session.php")
  .then(function (response) {
    return response.json();
  })
  .then(function (data) {

    const nav = document.querySelector("nav");

    if (!nav) {
      return;
    }

    if (data.connecte) {

      /* Supprime Connexion */
      const lienConnexion = nav.querySelector(
        'a[href="Connection.html"]'
      );

      if (lienConnexion) {
        lienConnexion.remove();
      }

      /* Vérifie si lien admin déjà présent */
      const adminLinkExiste = nav.querySelector(
        'a[href="Admin.html"]'
      );

      /* Si admin → ajoute lien admin */
      if (data.admin && !adminLinkExiste) {

        const adminLink = document.createElement("a");

        adminLink.href = "AdminDashboard.html";
        adminLink.textContent = "Administration";

        nav.appendChild(adminLink);
      }

      /* Bloc utilisateur */
      const userMenu = document.createElement("div");

      userMenu.classList.add("user-menu");

      userMenu.innerHTML =
        "<span>Bonjour " +
        data.prenom +
        "</span>" +
        '<a href="php/Deconnexion.php">Déconnexion</a>';

      nav.after(userMenu);
    }
  })
  .catch(function () {
    console.log("Erreur session.");
  });