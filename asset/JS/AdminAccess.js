fetch("php/check_session.php")
  .then(function (response) {
    return response.json();
  })
  .then(function (data) {
    if (!data.connecte || !data.admin) {
      alert("Accès refusé.");
      window.location.href = "PagePrincipale.html";
    }
  })
  .catch(function () {
    window.location.href = "PagePrincipale.html";
  });