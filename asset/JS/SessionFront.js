fetch("PHP/Check_Session.php")
  .then(function (response) {
    if (!response.ok) {
      throw new Error("Erreur lors de la vérification de session.");
    }

    return response.json();
  })
  .then(function (data) {
    const nav = document.querySelector("nav");

    if (!nav) {
      return;
    }

    if (!data.connecte) {
      return;
    }

    // Supprime le lien Connexion
    const lienConnexion = nav.querySelector(
      'a[href="Connection.html"]'
    );

    if (lienConnexion) {
      lienConnexion.remove();
    }

    // Ajoute le lien Administration si nécessaire
    const adminLinkExiste = nav.querySelector(
      'a[href="AdminDashboard.html"]'
    );

    if (data.admin && !adminLinkExiste) {
      const adminLink = document.createElement("a");

      adminLink.href = "AdminDashboard.html";
      adminLink.textContent = "Administration";

      nav.appendChild(adminLink);
    }

    // Évite de créer plusieurs menus utilisateur
    if (document.querySelector(".user-menu")) {
      return;
    }

    // Création sécurisée du bloc utilisateur
    const userMenu = document.createElement("div");
    userMenu.classList.add("user-menu");

    const bienvenue = document.createElement("span");
    bienvenue.textContent = "Bonjour " + (data.prenom ?? "");

    const deconnexion = document.createElement("a");
    deconnexion.href = "PHP/Deconnexion.php";
    deconnexion.textContent = "Déconnexion";

    userMenu.appendChild(bienvenue);
    userMenu.appendChild(deconnexion);

    nav.after(userMenu);
  })
  .catch(function (error) {
    console.error("Erreur session :", error);
  });