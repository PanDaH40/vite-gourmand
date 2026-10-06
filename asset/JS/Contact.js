document.addEventListener("DOMContentLoaded", function () {

    const form =
        document.getElementById("contactForm");

    const messageRetour =
        document.getElementById("contactMessage");


    if (!form || !messageRetour) {
        return;
    }


    form.addEventListener(
        "submit",
        async function (event) {

            event.preventDefault();


            const email =
                document.getElementById("email").value.trim();

            const titre =
                document.getElementById("titre").value.trim();

            const message =
                document.getElementById("message").value.trim();


            if (
                email === "" ||
                titre === "" ||
                message === ""
            ) {

                messageRetour.textContent =
                    "Tous les champs sont obligatoires.";

                return;
            }


            messageRetour.textContent =
                "Envoi en cours...";


            try {

                const response = await fetch(
                    "PHP/Contact.php",
                    {
                        method: "POST",

                        headers: {
                            "Content-Type": "application/json"
                        },

                        body: JSON.stringify({
                            email: email,
                            titre: titre,
                            message: message
                        })
                    }
                );


                const data =
                    await response.json();


                if (!response.ok || !data.success) {

                    throw new Error(
                        data.error ||
                        "Impossible de traiter votre demande."
                    );
                }


                messageRetour.textContent =
                    data.message;

                form.reset();


            } catch (error) {

                console.error(error);

                messageRetour.textContent =
                    error.message;
            }
        }
    );

});