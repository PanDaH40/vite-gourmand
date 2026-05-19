const boutonsSuppression = document.querySelectorAll(".btn-danger");

boutonsSuppression.forEach(function (bouton) {

  bouton.addEventListener("click", function (e) {

    const confirmation = confirm(
      "Êtes-vous sûr de vouloir effectuer cette action ?"
    );

    if (!confirmation) {
      e.preventDefault();
    }

  });

});

const boutonsValidation = document.querySelectorAll(".btn-small");

boutonsValidation.forEach(function (bouton) {

  if (!bouton.classList.contains("btn-danger")) {

    bouton.addEventListener("click", function () {

      const texte = bouton.textContent.trim();

      if (
        texte === "Accepter" ||
        texte === "Retour effectué" ||
        texte === "Modifier" ||
        texte === "Terminer"
      ) {

        alert("Action effectuée avec succès.");

      }

    });

  }

});