const menusTableBody = document.getElementById("menusTableBody");

const menuForm = document.getElementById("menuForm");
const menuId = document.getElementById("menuId");

const titre = document.getElementById("titre");
const prix = document.getElementById("prix");
const stock = document.getElementById("stock");
const minimum = document.getElementById("minimum");
const regime = document.getElementById("regime");
const description = document.getElementById("description");

const formTitre = document.getElementById("formTitre");
const btnEnregistrer = document.getElementById("btnEnregistrer");
const btnAnnuler = document.getElementById("btnAnnulerModification");

let menusActuels = [];

/*
 * Charge les menus depuis MySQL
 */
function chargerMenus() {
  fetch("PHP/Get_Menus.php")
    .then(function (response) {
      if (!response.ok) {
        throw new Error("Impossible de charger les menus.");
      }

      return response.json();
    })

    .then(function (menus) {
      menusActuels = menus;

      afficherMenus(menus);
    })

    .catch(function (error) {
      console.error(error);

      menusTableBody.innerHTML = `
        <tr>
          <td colspan="6">
            Impossible de charger les menus.
          </td>
        </tr>
      `;
    });
}

/*
 * Affiche les menus
 */
function afficherMenus(menus) {
  menusTableBody.innerHTML = "";

  if (menus.length === 0) {
    menusTableBody.innerHTML = `
      <tr>
        <td colspan="6">
          Aucun menu enregistré.
        </td>
      </tr>
    `;

    return;
  }

  menus.forEach(function (menu) {
    const tr = document.createElement("tr");

    tr.innerHTML = `
      <td>
        ${menu.titre}
      </td>

      <td>
        ${parseFloat(menu.prix_par_personne).toFixed(2)} €
      </td>

      <td>
        ${menu.nombre_personne_minimum}
      </td>

      <td>
        ${menu.quantite_restante}
      </td>

      <td>
        ${menu.regime}
      </td>

      <td>

        <div class="table-actions">

          <button
            type="button"
            class="btn-small btn-modifier"
            data-id="${menu.menu_id}">
            Modifier
          </button>

          <button
            type="button"
            class="btn-small btn-danger btn-supprimer"
            data-id="${menu.menu_id}">
            Supprimer
          </button>

        </div>

      </td>
    `;

    menusTableBody.appendChild(tr);
  });
}

/*
 * Clic sur Modifier / Supprimer
 */
menusTableBody.addEventListener("click", function (event) {
  const bouton = event.target.closest("button");

  if (!bouton) {
    return;
  }

  const id = parseInt(bouton.dataset.id);

  const menu = menusActuels.find(function (element) {
    return parseInt(element.menu_id) === id;
  });

  if (!menu) {
    return;
  }

  /*
   * MODIFIER
   */
  if (bouton.classList.contains("btn-modifier")) {
    menuId.value = menu.menu_id;

    titre.value = menu.titre;

    prix.value = menu.prix_par_personne;

    stock.value = menu.quantite_restante;

    minimum.value = menu.nombre_personne_minimum;

    regime.value = menu.regime;

    description.value = menu.description;

    formTitre.textContent = "Modifier le menu";

    btnEnregistrer.textContent = "Enregistrer les modifications";

    btnAnnuler.hidden = false;

    menuForm.scrollIntoView({
      behavior: "smooth",
    });
  }

  if (bouton.classList.contains("btn-supprimer")) {
    const confirmation = confirm(
      'Voulez-vous vraiment supprimer le menu "' + menu.titre + '" ?',
    );

    if (!confirmation) {
      return;
    }

    fetch("PHP/AdminSupprimerMenu.php", {
      method: "POST",

      headers: {
        "Content-Type": "application/json",
      },

      body: JSON.stringify({
        menu_id: menu.menu_id,
      }),
    })
      .then(function (response) {
        return response.text().then(function (texte) {
          let data;

          try {
            data = JSON.parse(texte);
          } catch (error) {
            console.error("Réponse PHP :", texte);

            throw new Error("Le serveur n'a pas renvoyé une réponse valide.");
          }

          if (!response.ok) {
            throw new Error(data.message || "Impossible de supprimer le menu.");
          }

          return data;
        });
      })

      .then(function (data) {
        alert(data.message);

        /*
         * Recharge le tableau depuis MySQL.
         */
        chargerMenus();
      })

      .catch(function (error) {
        console.error(error);

        alert(error.message);
      });
  }
});

/*
 * Envoi du formulaire
 */
menuForm.addEventListener("submit", function (event) {
  /*
   * Aucun menu_id :
   * il s'agit d'un AJOUT.
   *
   * On laisse le formulaire aller
   * normalement vers AjouterMenu.php.
   */
  if (menuId.value === "") {
    return;
  }

  /*
   * Un menu_id existe :
   * il s'agit d'une MODIFICATION.
   */
  event.preventDefault();

  const donnees = {
    menu_id: parseInt(menuId.value),

    titre: titre.value.trim(),

    prix: parseFloat(prix.value),

    stock: parseInt(stock.value),

    minimum: parseInt(minimum.value),

    regime: regime.value,

    description: description.value.trim(),
  };

  fetch("PHP/AdminModifierMenu.php", {
    method: "POST",

    headers: {
      "Content-Type": "application/json",
    },

    body: JSON.stringify(donnees),
  })
    .then(function (response) {
      return response.text().then(function (texte) {
        let data;

        try {
          data = JSON.parse(texte);
        } catch (error) {
          console.error("Réponse PHP reçue :", texte);

          throw new Error(
            "Le serveur n'a pas renvoyé une réponse JSON valide.",
          );
        }

        if (!response.ok) {
          throw new Error(data.message || "Erreur lors de la modification.");
        }

        return data;
      });
    })

    .then(function (data) {
      if (!data.success) {
        alert(data.message);

        return;
      }

      alert("Menu modifié avec succès.");

      /*
       * Réinitialisation du formulaire
       */
      menuForm.reset();

      menuId.value = "";

      formTitre.textContent = "Ajouter un menu";

      btnEnregistrer.textContent = "Ajouter le menu";

      btnAnnuler.hidden = true;

      /*
       * Recharge les données MySQL
       */
      chargerMenus();
    })

    .catch(function (error) {
      console.error(error);

      alert(error.message);
    });
});

/*
 * Annuler la modification
 */
btnAnnuler.addEventListener("click", function () {
  menuForm.reset();

  menuId.value = "";

  formTitre.textContent = "Ajouter un menu";

  btnEnregistrer.textContent = "Ajouter le menu";

  btnAnnuler.hidden = true;
});

/*
 * Chargement initial
 */
chargerMenus();
