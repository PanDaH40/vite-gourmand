const commandesAttente =
  document.getElementById("commandesAttente");

const commandesAcceptees =
  document.getElementById("commandesAcceptees");

const menusDisponibles =
  document.getElementById("menusDisponibles");

const materielRecuperer =
  document.getElementById("materielRecuperer");

const dernieresCommandes =
  document.getElementById("dernieresCommandes");


/*
 * Format :
 * 2026-10-23 -> 23/10/2026
 */
function formaterDate(dateSQL) {

  if (!dateSQL) {
    return "";
  }

  const morceaux = dateSQL.split("-");

  if (morceaux.length !== 3) {
    return dateSQL;
  }

  return (
    morceaux[2] +
    "/" +
    morceaux[1] +
    "/" +
    morceaux[0]
  );
}


/*
 * Classe CSS du statut.
 */
function getBadgeClass(statut) {

  const valeur =
    statut.toLowerCase();

  if (
    valeur === "acceptée" ||
    valeur === "acceptee"
  ) {
    return "accepted";
  }

  if (
    valeur === "terminée" ||
    valeur === "terminee"
  ) {
    return "done";
  }

  if (
    valeur === "refusée" ||
    valeur === "refusee"
  ) {
    return "refused";
  }

  return "pending";
}


/*
 * STATISTIQUES
 */
fetch("PHP/AdminStats.php")

  .then(function (response) {

    if (!response.ok) {
      throw new Error(
        "Impossible de charger les statistiques."
      );
    }

    return response.json();

  })

  .then(function (stats) {

    if (stats.erreur) {

      console.error(stats.erreur);

      return;
    }

    commandesAttente.textContent =
      stats.commandes_attente;

    commandesAcceptees.textContent =
      stats.commandes_acceptees;

    menusDisponibles.textContent =
      stats.menus_disponibles;

    materielRecuperer.textContent =
      stats.materiel_a_recuperer;

  })

  .catch(function (error) {

    console.error(error);

  });


/*
 * DERNIÈRES COMMANDES
 */
fetch("PHP/AdminCommandes.php")

  .then(function (response) {

    if (!response.ok) {

      throw new Error(
        "Impossible de charger les commandes."
      );
    }

    return response.json();

  })

  .then(function (commandes) {

    if (commandes.erreur) {

      dernieresCommandes.innerHTML = `
        <tr>
          <td colspan="5">
            ${commandes.erreur}
          </td>
        </tr>
      `;

      return;
    }


    dernieresCommandes.innerHTML = "";


    /*
     * On affiche uniquement
     * les 5 dernières commandes.
     */
    const commandesRecentes =
      commandes.slice(0, 5);


    if (commandesRecentes.length === 0) {

      dernieresCommandes.innerHTML = `
        <tr>
          <td colspan="5">
            Aucune commande enregistrée.
          </td>
        </tr>
      `;

      return;
    }


    commandesRecentes.forEach(
      function (commande) {

        const ligne =
          document.createElement("tr");

        const badgeClass =
          getBadgeClass(
            commande.statut
          );


        ligne.innerHTML = `
          <td>
            ${commande.numero_commande}
          </td>

          <td>
            ${commande.prenom}
          </td>

          <td>
            ${commande.titre}
          </td>

          <td>
            ${formaterDate(
              commande.date_prestation
            )}
          </td>

          <td>
            <span class="badge ${badgeClass}">
              ${commande.statut}
            </span>
          </td>
        `;


        dernieresCommandes.appendChild(
          ligne
        );

      }
    );

  })

  .catch(function (error) {

    console.error(error);

    dernieresCommandes.innerHTML = `
      <tr>
        <td colspan="5">
          Impossible de charger les commandes.
        </td>
      </tr>
    `;

  });