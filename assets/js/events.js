

document.querySelectorAll(".menu-link").forEach(link => {
    link.addEventListener("click", function (event) {
        event.preventDefault(); // Evita que el enlace se ejecute directamente

        const token = localStorage.getItem("jwt");
        if (!token) {
            window.location.href = "login.php"; // Si no hay token, redirige al login
            return;
        }

        // Validar el token con el backend antes de redirigir
        fetch("http://tuservidor.com/api/validate_token.php", {
            method: "POST",
            headers: {
                "Authorization": `Bearer ${token}`,
                "Content-Type": "application/json"
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.valid) {
                    window.location.href = link.getAttribute("href"); // Redirigir solo si es válido
                } else {
                    alert("Sesión expirada. Inicia sesión de nuevo.");
                    localStorage.removeItem("jwt");
                    window.location.href = "login.php";
                }
            })
            .catch(error => console.error("Error al validar sesión", error));
    });
});