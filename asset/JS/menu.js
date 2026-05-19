const prixMin = document.getElementById("prix-min");
const prixMax = document.getElementById("prix-max");
const theme = document.getElementById("theme");
const regime = document.getElementById("regime");
const nbMin = document.getElementById("nb-min");

const cards = document.querySelectorAll(".card");
const resultCount = document.getElementById("result-count");

function filtrerMenus() {
  const valeurPrixMin = parseInt(prixMin.value);
  const valeurPrixMax = parseInt(prixMax.value);
  const valeurTheme = theme.value.toLowerCase();
  const valeurRegime = regime.value.toLowerCase();
  const valeurNbMin = parseInt(nbMin.value);

  let totalVisible = 0;

  cards.forEach(function (card) {
    const prixMenu = parseInt(card.dataset.price);
    const themeMenu = card.dataset.theme.toLowerCase();
    const regimeMenu = card.dataset.regime.toLowerCase();
    const minPersonnes = parseInt(card.dataset.min);

    let afficher = true;

    if (!isNaN(valeurPrixMin) && prixMenu < valeurPrixMin) {
      afficher = false;
    }

    if (!isNaN(valeurPrixMax) && prixMenu > valeurPrixMax) {
      afficher = false;
    }

    if (valeurTheme !== "" && themeMenu !== valeurTheme) {
      afficher = false;
    }

    if (valeurRegime !== "" && regimeMenu !== valeurRegime) {
      afficher = false;
    }

    if (!isNaN(valeurNbMin) && minPersonnes < valeurNbMin) {
      afficher = false;
    }

    if (afficher) {
      card.style.display = "block";
      totalVisible++;
    } else {
      card.style.display = "none";
    }
  });

  resultCount.textContent = totalVisible + " menu(s) affiché(s)";
}

prixMin.addEventListener("input", filtrerMenus);
prixMax.addEventListener("input", filtrerMenus);
theme.addEventListener("change", filtrerMenus);
regime.addEventListener("change", filtrerMenus);
nbMin.addEventListener("input", filtrerMenus);

filtrerMenus();