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


/*
 * Informations détaillées qui étaient auparavant
 * présentes directement dans MenuPaque.html,
 * MenuNoel.html et MenuClassique.html.
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

        plats: [
            {
                type: "Entrée",
                nom: "Salade de saison, vinaigrette maison",
                allergenes: "moutarde"
            },
            {
                type: "Plat",
                nom: "Agneau confit accompagné de légumes rôtis",
                allergenes: "aucun"
            },
            {
                type: "Dessert",
                nom: "Moelleux chocolat et crème légère",
                allergenes: "œufs, lait"
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

        plats: [
            {
                type: "Entrée",
                nom: "Foie gras maison, pain toasté",
                allergenes: "gluten"
            },
            {
                type: "Plat",
                nom: "Dinde rôtie, pommes grenailles",
                allergenes: "aucun"
            },
            {
                type: "Dessert",
                nom: "Bûche chocolat praliné",
                allergenes: "lait, œufs"
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

        plats: [
            {
                type: "Entrée",
                nom: "Salade de saison, vinaigrette maison",
                allergenes: "moutarde"
            },
            {
                type: "Plat",
                nom: "Bœuf braisé accompagné de légumes rôtis",
                allergenes: "aucun"
            },
            {
                type: "Dessert",
                nom: "Tarte aux pommes maison",
                allergenes: "gluten, œufs"
            }
        ],

        conditions: [
            "Commande minimum 5 jours avant la prestation.",
            "Conservation au frais obligatoire après livraison.",
            "Retour de matériel possible selon prestation."
        ]
    }
};


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


            /*
             * Si le menu possède les informations détaillées
             * de nos anciens fichiers HTML.
             */
            if (detail) {

                menuInfos.textContent =
                    "Thème : " +
                    detail.theme +
                    " • Régime : " +
                    menu.regime;


                // Galerie
                detail.images.forEach(function (image) {

                    const img =
                        document.createElement("img");

                    img.src = image.src;
                    img.alt = image.alt;
                    img.classList.add("gallery-item");

                    menuGallery.appendChild(img);
                });


                // Plats
                detail.plats.forEach(function (plat) {

                    const article =
                        document.createElement("article");

                    article.classList.add("dish");

                    article.innerHTML =
                        "<h4>" + plat.type + "</h4>" +
                        "<p>" + plat.nom + "</p>" +
                        '<p class="muted small">' +
                        "<strong>Allergènes :</strong> " +
                        plat.allergenes +
                        "</p>";

                    menuPlats.appendChild(article);
                });


                // Conditions
                detail.conditions.forEach(function (condition) {

                    const li =
                        document.createElement("li");

                    li.textContent = condition;

                    menuConditions.appendChild(li);
                });

            } else {

                /*
                 * Pour un nouveau menu créé par l'admin,
                 * comme Menu Anniversaire.
                 */

                menuInfos.textContent =
                    "Régime : " + menu.regime;

                menuGallery.innerHTML =
                    '<p class="muted">Aucune photo disponible pour ce menu.</p>';

                menuPlats.innerHTML =
                    '<p class="muted">Les plats de ce menu seront prochainement détaillés.</p>';

                menuConditions.innerHTML =
                    "<li>Commande à effectuer avant la date de prestation.</li>" +
                    "<li>Conservation au frais après livraison.</li>";
            }

        })

        .catch(function (error) {

            console.error(error);

            menuTitre.textContent =
                "Menu introuvable";

            menuDescription.textContent =
                "Impossible de charger les informations de ce menu.";

        });

}