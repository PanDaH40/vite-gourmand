const form = document.querySelector(".auth-form");

const email = document.getElementById("email");
const password = document.getElementById("password");

const messageErreur = document.getElementById("messageErreur");

function afficherErreur(message){
    messageErreur.textContent = message;
}

form.addEventListener("submit", function(e){

    messageErreur.textContent = "";

    if(email.value.trim() === "" || password.value.trim() === ""){
        e.preventDefault();
        afficherErreur("Veuillez remplir tous les champs.");
        return;
    }

    if(!email.value.includes("@")){
        e.preventDefault();
        afficherErreur("Adresse email invalide.");
        return;
    }

});