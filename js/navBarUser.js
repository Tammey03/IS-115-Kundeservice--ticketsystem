/*
    Vent til HTML-siden er ferdig lastet
    før JavaScript begynner.
*/
document.addEventListener("DOMContentLoaded", () => {


    /*
        Finn alle <a>-elementer som ligger
        inne i .nav-links.
    */
    const navLinks =
        document.querySelectorAll(".nav-links a");


    /*
        Finn navnet på siden brukeren
        befinner seg på.

        Eksempel:

        /prosjekt/pages/bruker/mainUser.php

        blir:

        mainUser.php
    */
    const currentPage =
        window.location.pathname.split("/").pop();


    /*
        Gå gjennom hver lenke i navbaren.
    */
    navLinks.forEach((link) => {


        /*
            Finn href-verdien til lenken.

            Eksempel:

            <a href="mineSaker.php">

            gir:

            mineSaker.php
        */
        const linkPage =
            link.getAttribute("href");


        /*
            Hvis lenken peker til siden
            brukeren er på...
        */
        if (linkPage === currentPage) {


            /*
                ...legg til CSS-klassen "active".
            */
            link.classList.add("active");

        }

    });

});