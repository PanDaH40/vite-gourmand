
const params = new URLSearchParams(window.location.search);
const menuId = params.get("id");

const menuTitre = document.getElementById("menu-titre");
const menuInfos = document.getElementById("menu-infos");
const menuMinimum = document.getElementById("menu-minimum");
const menuPrixMinimum = document.getElementById("menu-prix-minimum");
const menuStock = document.getElementById("menu-stock");
const menuDescription = document.getElementById("menu-description");
const menuGallery = document.getElementById("menu-gallery");
const menuPlats = document.getElementById("menu-plats");
const menuConditions = document.getElementById("menu-conditions");
const commanderMenu = document.getElementById("commander-menu");


/**
 * Informations détaillées des anciens menus.
 * On conserve les images et les conditions.
 *
 * Les plats ne sont plus écrits ici :
 * ils sont maintenant récupérés depuis MySQL.
 */
const detailsMenus = {

    "Menu Pâques": {

        theme: "Pâques",

        images: [
            {
                src: "asset/Images/salade_saison_2.jpg",
                alt: "Entrée du menu Pâques"
            },
            {
                src: "asset/Images/agneau.jpg",
                alt: "Plat du menu Pâques"
            },
            {
                src: "asset/Images/moelleux.jpg",
                alt: "Dessert du menu Pâques"
            }
        ],

        conditions: [
            "Commande minimum 7 jours avant la prestation.",
            "Conservation au frais obligatoire après livraison.",
            "Retour du matériel possible selon prestation."
        ]
    },


    "Menu Noël": {

        theme: "Noël",

        images: [
            {
                src: "asset/Images/foie_gras.jpg",
                alt: "Entrée du menu Noël"
            },
            {
                src: "asset/Images/dinde.jpg",
                alt: "Plat du menu Noël"
            },
            {
                src: "asset/Images/buche.jpg",
                alt: "Dessert du menu Noël"
            }
        ],

        conditions: [
            "Commande minimum 7 jours avant la prestation.",
            "Conservation au frais obligatoire après livraison.",
            "Retour de matériel possible selon prestation."
        ]
    },


    "Menu Classique": {

        theme: "Classique",

        images: [
            {
                src: "asset/Images/salade_saison_2.jpg",
                alt: "Entrée du menu Classique"
            },
            {
                src: "asset/Images/boeuf_braisé.jpg",
                alt: "Plat du menu Classique"
            },
            {
                src: "asset/Images/tarte_pomme.jpg",
                alt: "Dessert du menu Classique"
            }
        ],

        conditions: [
            "Commande minimum 5 jours avant la prestation.",
            "Conservation au frais obligatoire après livraison.",
            "Retour de matériel possible selon prestation."
        ]
    }
};


/**
 * Chargement des vrais plats associés au menu.
 */
function chargerPlatsMenu(id) {

    menuPlats.textContent = "Chargement des plats...";

    fetch("PHP/Get_Plats_Menu.php?id=" + encodeURIComponent(id))

        .then(function (response) {

            if (!response.ok) {
                throw new Error("Impossible de charger les plats.");
            }

            return response.json();
        })

        .then(function (plats) {

            if (!Array.isArray(plats)) {
                throw new Error("Réponse des plats invalide.");
            }

            menuPlats.innerHTML = "";

            if (plats.length === 0) {

                menuPlats.innerHTML =
                    '<p class="muted">Aucun plat associé à ce menu pour le moment.</p>';

                return;
            }

            plats.forEach(function (plat) {

                const article = document.createElement("article");
                article.classList.add("dish");

                const titre = document.createElement("h4");
                titre.textContent = plat.titre_plat;

                article.appendChild(titre);
                menuPlats.appendChild(article);
            });
        })

        .catch(function (error) {

            console.error(error);

            menuPlats.textContent =
                "Impossible de charger les plats de ce menu.";
        });
}


/**
 * Chargement des informations du menu.
 */
if (!menuId) {

    menuTitre.textContent = "Menu introuvable";

} else {

    fetch("PHP/Get_Menus.php?id=" + encodeURIComponent(menuId))

        .then(function (response) {

            if (!response.ok) {
                throw new Error("Menu introuvable");
            }

            return response.json();
        })

        .then(function (menu) {

            const detail = detailsMenus[menu.titre];

            // Informations venant de MySQL
            menuTitre.textContent = menu.titre;

            menuMinimum.textContent =
                menu.nombre_personne_minimum + " personnes";

            const prixMinimum =
                menu.nombre_personne_minimum *
                menu.prix_par_personne;

            menuPrixMinimum.textContent =
                prixMinimum.toFixed(2) + " €";

            menuStock.textContent =
                menu.quantite_restante;

            menuDescription.textContent =
                menu.description;

            commanderMenu.href =
                "Commande.html?menu=" + menu.menu_id;


            /**
             * Informations conservées des anciens menus.
             */
            if (detail) {

                menuInfos.textContent =
                    "Thème : " +
                    detail.theme +
                    " • Régime : " +
                    menu.regime;


                // Galerie
                detail.images.forEach(function (image) {

                    const img = document.createElement("img");

                    img.src = image.src;
                    img.alt = image.alt;
                    img.classList.add("gallery-item");

                    menuGallery.appendChild(img);
                });


                // Conditions
                detail.conditions.forEach(function (condition) {

                    const li = document.createElement("li");

                    li.textContent = condition;

                    menuConditions.appendChild(li);
                });

            } else {

                /**
                 * Nouveau menu créé par l'administration.
                 */
                menuInfos.textContent =
                    "Régime : " + menu.regime;

                menuGallery.innerHTML =
                    '<p class="muted">Aucune photo disponible pour ce menu.</p>';

                menuConditions.innerHTML =
                    "<li>Commande à effectuer avant la date de prestation.</li>" +
                    "<li>Conservation au frais après livraison.</li>";
            }


            /**
             * Tous les menus utilisent désormais
             * les plats enregistrés dans MySQL.
             */
            chargerPlatsMenu(menu.menu_id);

        })

        .catch(function (error) {

            console.error(error);

            menuTitre.textContent =
                "Menu introuvable";

            menuDescription.textContent =
                "Impossible de charger les informations de ce menu.";

        });

}
