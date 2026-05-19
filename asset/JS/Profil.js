const forms = document.querySelectorAll(".profile-form");

const profilForm = forms[0];
const passwordForm = forms[1];

const prenom = document.getElementById("prenom");
const nom = document.getElementById("nom");
const email = document.getElementById("email");
const telephone = document.getElementById("telephone");
const adresse = document.getElementById("adresse");
const ville = document.getElementById("ville");
const pays = document.getElementById("pays");

const currentPassword = document.getElementById("current-password");
const newPassword = document.getElementById("new-password");
const confirmPassword = document.getElementById("confirm-password");

function motDePasseValide(mdp) {
  const longueur = mdp.length >= 10;
  const majuscule = /[A-Z]/.test(mdp);
  const minuscule = /[a-z]/.test(mdp);
  const chiffre = /[0-9]/.test(mdp);
  const special = /[^A-Za-z0-9]/.test(mdp);

  return longueur && majuscule && minuscule && chiffre && special;
}

profilForm.addEventListener("submit", function (e) {
  if (
    prenom.value.trim() === "" ||
    nom.value.trim() === "" ||
    email.value.trim() === "" ||
    telephone.value.trim() === "" ||
    adresse.value.trim() === "" ||
    ville.value.trim() === "" ||
    pays.value.trim() === ""
  ) {
    e.preventDefault();
    alert("Veuillez remplir toutes vos informations personnelles.");
    return;
  }

  if (!email.value.includes("@")) {
    e.preventDefault();
    alert("Veuillez saisir une adresse email valide.");
    return;
  }

  alert("Informations du profil vérifiées.");
});

passwordForm.addEventListener("submit", function (e) {
  if (
    currentPassword.value.trim() === "" ||
    newPassword.value.trim() === "" ||
    confirmPassword.value.trim() === ""
  ) {
    e.preventDefault();
    alert("Veuillez remplir tous les champs du mot de passe.");
    return;
  }

  if (!motDePasseValide(newPassword.value)) {
    e.preventDefault();
    alert(
      "Le nouveau mot de passe doit contenir au moins 10 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial."
    );
    return;
  }

  if (newPassword.value !== confirmPassword.value) {
    e.preventDefault();
    alert("Les mots de passe ne correspondent pas.");
    return;
  }

  alert("Mot de passe vérifié.");
});