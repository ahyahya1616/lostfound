document.addEventListener("DOMContentLoaded", function () {
    // Gestion du dropdown Actions
    document.getElementById("user-action").addEventListener("click", function () {
        document.getElementById("dropdown2").classList.toggle("hidden");
    });

    // Gestion du dropdown Profil
    document.getElementById("user-profile").addEventListener("click", function () {
        document.getElementById("dropdown-profile").classList.toggle("hidden");
    });

    // Fermer les dropdowns lorsqu'on clique ailleurs
    document.addEventListener("click", function (event) {
        if (!document.getElementById("user-action").contains(event.target)) {
            document.getElementById("dropdown2").classList.add("hidden");
        }
        if (!document.getElementById("user-profile").contains(event.target)) {
            document.getElementById("dropdown-profile").classList.add("hidden");
        }
    });
});