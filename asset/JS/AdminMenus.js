const tbody = document.querySelector("tbody");

fetch("php/get_menus.php")
  .then(function (response) {
    return response.json();
  })
  .then(function (menus) {
    tbody.innerHTML = "";

    menus.forEach(function (menu) {
      const tr = document.createElement("tr");

      tr.innerHTML =
        "<td>" + menu.titre + "</td>" +
        "<td>" + menu.prix_par_personne + " €</td>" +
        "<td>" + menu.nombre_personne_minimum + "</td>" +
        "<td>" + menu.quantite_restante + "</td>" +
        "<td>" + menu.regime + "</td>" +
        "<td>" +
          '<div class="table-actions">' +
            '<button type="button" class="btn-small">Modifier</button>' +
            '<button type="button" class="btn-small btn-danger">Supprimer</button>' +
          "</div>" +
        "</td>";

      tbody.appendChild(tr);
    });
  });