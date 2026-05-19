const form = document.querySelector(".auth-form");

const nom = document.getElementById("nom");
const prenom = document.getElementById("prenom");
const email = document.getElementById("email");
const telephone = document.getElementById("telephone");
const adresse = document.getElementById("adresse");
const ville = document.getElementById("ville");
const pays = document.getElementById("pays");
const password = document.getElementById("password");
const password2 = document.getElementById("password2");

const messageErreur = document.getElementById("messageErreur");
const messageSucces = document.getElementById("messageSucces");

function afficherErreur(message) {
  messageErreur.textContent = message;
  messageSucces.textContent = "";
}

function afficherSucces(message) {
  messageSucces.textContent = message;
  messageErreur.textContent = "";
}

function motDePasseValide(mdp) {
  const longueur = mdp.length >= 10;
  const majuscule = /[A-Z]/.test(mdp);
  const minuscule = /[a-z]/.test(mdp);
  const chiffre = /[0-9]/.test(mdp);
  const special = /[^A-Za-z0-9]/.test(mdp);

  return longueur && majuscule && minuscule && chiffre && special;
}

form.addEventListener("submit", function (e) {

  messageErreur.textContent = "";
  messageSucces.textContent = "";

  if (
    nom.value.trim() === "" ||
    prenom.value.trim() === "" ||
    email.value.trim() === "" ||
    telephone.value.trim() === "" ||
    adresse.value.trim() === "" ||
    ville.value.trim() === "" ||
    pays.value.trim() === "" ||
    password.value.trim() === "" ||
    password2.value.trim() === ""
  ) {
    e.preventDefault();
    afficherErreur("Merci de remplir tous les champs obligatoires.");
    return;
  }

  if (!email.value.includes("@")) {
    e.preventDefault();
    afficherErreur("Veuillez saisir une adresse mail valide.");
    return;
  }

  if (telephone.value.trim().length < 10) {
    e.preventDefault();
    afficherErreur("Veuillez saisir un numéro de téléphone valide.");
    return;
  }

  if (!motDePasseValide(password.value)) {
    e.preventDefault();
    afficherErreur("Le mot de passe doit contenir au moins 10 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.");
    return;
  }

  if (password.value !== password2.value) {
    e.preventDefault();
    afficherErreur("Les mots de passe ne correspondent pas.");
    return;
  }

});