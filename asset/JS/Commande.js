const menuSelect = document.getElementById("menuSelect");
const nbPersonnes = document.getElementById("nbPersonnes");
const ville = document.getElementById("ville");
const adresseLivraison = document.getElementById("adresseLivraison");

const nbVegetarien = document.getElementById("nbVegetarien");
const nbVegan = document.getElementById("nbVegan");

const adapteInfo = document.getElementById("adapteInfo");

const prixMenu = document.getElementById("prixMenu");
const reduction = document.getElementById("reduction");
const prixLivraison = document.getElementById("prixLivraison");
const total = document.getElementById("total");

const form = document.querySelector(".commande-form");

/*
 * Distance calculée automatiquement.
 * null = distance pas encore calculée.
 */
let distanceLivraisonKm = null;


/**
 * Récupère les informations du menu sélectionné.
 */
function getMenuData() {

    const option = menuSelect.options[menuSelect.selectedIndex];

    if (!option || option.value === "") {
        return {
            minPersons: 1,
            prixPersonne: 0
        };
    }

    let minPersons = parseInt(option.dataset.min);
    let prixPersonne = parseFloat(option.dataset.prix);

    if (isNaN(minPersons)) {
        minPersons = 1;
    }

    if (isNaN(prixPersonne)) {
        prixPersonne = 0;
    }

    return {
        minPersons: minPersons,
        prixPersonne: prixPersonne
    };
}


/**
 * Charge les menus depuis MySQL.
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

            menuSelect.innerHTML = "";

            menus.forEach(function (menu) {

                const option = document.createElement("option");

                option.value = menu.menu_id;
                option.textContent = menu.titre;

                option.dataset.min =
                    menu.nombre_personne_minimum;

                option.dataset.prix =
                    menu.prix_par_personne;

                menuSelect.appendChild(option);
            });


            /*
             * Récupère le menu présent dans l'URL.
             * Exemple :
             * Commande.html?menu=4
             */
            const params =
                new URLSearchParams(window.location.search);

            const menuId = params.get("menu");

            if (menuId) {

                const optionExiste =
                    Array.from(menuSelect.options).some(
                        function (option) {
                            return option.value === menuId;
                        }
                    );

                if (optionExiste) {
                    menuSelect.value = menuId;
                }
            }

            mettreAJourMinimum();
            calculer();
            updateAdapteInfo();
        })

        .catch(function (error) {

            console.error(
                "Erreur lors du chargement des menus :",
                error
            );

            menuSelect.innerHTML =
                '<option value="">Erreur de chargement</option>';
        });
}


/**
 * Adapte le minimum selon le menu.
 */
function mettreAJourMinimum() {

    const menu = getMenuData();

    nbPersonnes.min = menu.minPersons;

    if (
        nbPersonnes.value !== "" &&
        parseInt(nbPersonnes.value) < menu.minPersons
    ) {
        nbPersonnes.value = menu.minPersons;
    }
}


/**
 * Calcule le prix de la commande.
 */
function calculer() {

    const menu = getMenuData();

    let nb = parseInt(nbPersonnes.value);

    if (isNaN(nb)) {
        nb = 0;
    }


    /*
     * Prix avant réduction.
     */
    const prix = nb * menu.prixPersonne;


    /*
     * Réduction de 10 %
     * à partir de minimum + 5 personnes.
     */
    let reduc = 0;

    if (nb >= menu.minPersons + 5) {
        reduc = prix * 0.10;
    }


    /*
     * Livraison.
     */
    let livraison = 0;

    const villeNormalisee =
        ville.value.trim().toLowerCase();

    if (villeNormalisee === "bordeaux") {

        livraison = 0;

    } else if (distanceLivraisonKm !== null) {

        livraison =
            5 + (distanceLivraisonKm * 0.59);
    }


    /*
     * Total.
     */
    const totalPrix =
        prix - reduc + livraison;


    /*
     * Affichage.
     */
    prixMenu.textContent =
        prix.toFixed(2);

    reduction.textContent =
        reduc.toFixed(2);

    prixLivraison.textContent =
        livraison.toFixed(2);

    total.textContent =
        totalPrix.toFixed(2);
}


/**
 * Calcule automatiquement la distance routière
 * depuis le centre de Bordeaux.
 */
async function calculerDistanceLivraison() {

    const adresse = adresseLivraison.value.trim();
    const villeSaisie = ville.value.trim();

    /*
     * Bordeaux = livraison gratuite.
     */
    if (villeSaisie.toLowerCase() === "bordeaux") {

        distanceLivraisonKm = 0;

        calculer();

        return;
    }


    /*
     * On attend d'avoir une adresse et une ville.
     */
    if (adresse === "" || villeSaisie === "") {

        distanceLivraisonKm = null;

        calculer();

        return;
    }


    try {

        /*
         * 1 - Recherche des coordonnées
         * de l'adresse de livraison.
         */
        const recherche =
            adresse + ", " +
            villeSaisie + ", France";

        const urlGeocodage =
            "https://nominatim.openstreetmap.org/search" +
            "?format=json" +
            "&limit=1" +
            "&countrycodes=fr" +
            "&q=" +
            encodeURIComponent(recherche);

        const responseGeo =
            await fetch(urlGeocodage);

        if (!responseGeo.ok) {
            throw new Error(
                "Erreur lors de la recherche de l'adresse."
            );
        }

        const resultatGeo =
            await responseGeo.json();

        if (
            !Array.isArray(resultatGeo) ||
            resultatGeo.length === 0
        ) {
            throw new Error(
                "Adresse de livraison introuvable."
            );
        }


        const latitudeDestination =
            parseFloat(resultatGeo[0].lat);

        const longitudeDestination =
            parseFloat(resultatGeo[0].lon);


        /*
         * Coordonnées approximatives
         * du centre de Bordeaux.
         */
        const latitudeBordeaux = 44.8378;
        const longitudeBordeaux = -0.5792;


        /*
         * 2 - Calcul de l'itinéraire routier.
         *
         * OSRM attend :
         * longitude,latitude
         */
        const urlRoute =
            "https://router.project-osrm.org/route/v1/driving/" +
            longitudeBordeaux + "," +
            latitudeBordeaux + ";" +
            longitudeDestination + "," +
            latitudeDestination +
            "?overview=false";

        const responseRoute =
            await fetch(urlRoute);

        if (!responseRoute.ok) {
            throw new Error(
                "Impossible de calculer l'itinéraire."
            );
        }

        const resultatRoute =
            await responseRoute.json();

        if (
            resultatRoute.code !== "Ok" ||
            !resultatRoute.routes ||
            resultatRoute.routes.length === 0
        ) {
            throw new Error(
                "Aucun itinéraire trouvé."
            );
        }


        /*
         * OSRM retourne la distance en mètres.
         * Conversion en kilomètres.
         */
        distanceLivraisonKm =
            resultatRoute.routes[0].distance / 1000;


        console.log(
            "Distance de livraison :",
            distanceLivraisonKm.toFixed(2),
            "km"
        );


        calculer();

    } catch (error) {

        distanceLivraisonKm = null;

        console.error(
            "Erreur calcul livraison :",
            error
        );

        prixLivraison.textContent = "--";
    }
}


/**
 * Répartition classique /
 * végétarien / vegan.
 */
function updateAdapteInfo() {

    let totalPersonnes =
        parseInt(nbPersonnes.value);

    if (isNaN(totalPersonnes)) {
        totalPersonnes = 0;
    }


    let vegetarien =
        parseInt(nbVegetarien.value);

    let vegan =
        parseInt(nbVegan.value);


    if (isNaN(vegetarien)) {
        vegetarien = 0;
    }

    if (isNaN(vegan)) {
        vegan = 0;
    }


    if (vegetarien + vegan > totalPersonnes) {

        vegan = 0;
        vegetarien = totalPersonnes;
    }


    nbVegetarien.value = vegetarien;
    nbVegan.value = vegan;


    const classique =
        totalPersonnes -
        (vegetarien + vegan);


    adapteInfo.textContent =
        "Végétariens : " + vegetarien +
        " • Vegans : " + vegan +
        " • Classiques : " + classique;
}


/**
 * Vérification avant envoi.
 */
function verifierFormulaire(e) {

    const menu = getMenuData();


    if (menuSelect.value === "") {

        e.preventDefault();

        alert("Veuillez sélectionner un menu.");

        return;
    }


    if (nbPersonnes.value.trim() === "") {

        e.preventDefault();

        alert(
            "Veuillez indiquer le nombre de personnes."
        );

        return;
    }


    if (
        parseInt(nbPersonnes.value) <
        menu.minPersons
    ) {

        e.preventDefault();

        alert(
            "Le nombre minimum de personnes pour ce menu est de " +
            menu.minPersons +
            "."
        );

        return;
    }


    if (adresseLivraison.value.trim() === "") {

        e.preventDefault();

        alert(
            "Veuillez renseigner l'adresse de livraison."
        );

        return;
    }


    if (ville.value.trim() === "") {

        e.preventDefault();

        alert(
            "Veuillez renseigner la ville de livraison."
        );

        return;
    }


    /*
     * Hors Bordeaux, la distance
     * doit obligatoirement avoir été calculée.
     */
    if (
        ville.value.trim().toLowerCase() !== "bordeaux" &&
        distanceLivraisonKm === null
    ) {

        e.preventDefault();

        alert(
            "La distance de livraison n'a pas pu être calculée. " +
            "Vérifiez l'adresse et la ville."
        );

        return;
    }


    updateAdapteInfo();
    calculer();
}


/**
 * Changement de menu.
 */
menuSelect.addEventListener(
    "change",
    function () {

        mettreAJourMinimum();
        calculer();
        updateAdapteInfo();
    }
);


/**
 * Nombre de personnes.
 */
nbPersonnes.addEventListener(
    "input",
    function () {

        calculer();
        updateAdapteInfo();
    }
);


/**
 * Lorsque l'adresse change,
 * la distance précédente n'est plus valable.
 */
adresseLivraison.addEventListener(
    "input",
    function () {
        distanceLivraisonKm = null;
    }
);


/**
 * Calcul de la distance lorsque
 * l'utilisateur quitte le champ adresse.
 */
adresseLivraison.addEventListener(
    "change",
    calculerDistanceLivraison
);


/**
 * Lorsque la ville change,
 * la distance précédente n'est plus valable.
 */
ville.addEventListener(
    "input",
    function () {

        distanceLivraisonKm = null;

        calculer();
    }
);


/**
 * Calcul lorsque l'utilisateur
 * quitte le champ ville.
 */
ville.addEventListener(
    "change",
    calculerDistanceLivraison
);


/**
 * Menus végétariens.
 */
nbVegetarien.addEventListener(
    "input",
    updateAdapteInfo
);


/**
 * Menus vegans.
 */
nbVegan.addEventListener(
    "input",
    updateAdapteInfo
);


/**
 * Validation du formulaire.
 */
form.addEventListener(
    "submit",
    verifierFormulaire
);


/**
 * Chargement initial des menus.
 */
chargerMenus();


/**
 * Préremplit les informations
 * du client connecté.
 */
async function chargerInformationsClient() {

    try {

        const response =
            await fetch("PHP/Profil.php", {
                credentials: "same-origin"
            });

        if (!response.ok) {
            throw new Error(
                "Impossible de récupérer le profil."
            );
        }

        const data =
            await response.json();

        if (!data.success || !data.utilisateur) {
            throw new Error(
                "Profil utilisateur introuvable."
            );
        }

        const utilisateur =
            data.utilisateur;


        /*
         * Informations personnelles.
         */
        document.getElementById(
            "clientPrenom"
        ).value =
            utilisateur.prenom || "";

        document.getElementById(
            "clientEmail"
        ).value =
            utilisateur.email || "";

        document.getElementById(
            "clientTelephone"
        ).value =
            utilisateur.telephone || "";


        /*
         * Adresse.
         */
        if (!adresseLivraison.value.trim()) {

            adresseLivraison.value =
                utilisateur.adresse_postale || "";
        }


        /*
         * Ville.
         */
        if (!ville.value.trim()) {

            ville.value =
                utilisateur.ville || "";
        }


        /*
         * Calcul automatique de la distance
         * après préremplissage.
         */
        await calculerDistanceLivraison();

    } catch (error) {

        console.error(
            "Erreur de préremplissage du client :",
            error
        );
    }
}


/**
 * Chargement des informations client.
 */
chargerInformationsClient();