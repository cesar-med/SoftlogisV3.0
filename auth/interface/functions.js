import routes from "../../assets/js/routes"

export default class AuthClass {

    static init() {
        document.getElementById("loginForm")?.addEventListener("submit", AuthClass.handleLogin);
    }

    static handleLogin(event) {
        event.preventDefault();
        const formData = new FormData(event.target);
        fetch(`${routes.API_BASE_URL}api/auth/login`, {
            method: "POST",
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.token) {
                    localStorage.setItem("jwt", data.token); // Almacena el token en el navegador
                    const prevPage = sessionStorage.getItem('prevPage');

                    if (prevPage) {
                        try {
                            const prevUrl = new URL(prevPage);
                            if (prevUrl.origin === window.location.origin) {
                                window.location.href = prevPage;
                            } else {
                                // Si es otro dominio, redirecciona a una página segura o por defecto
                                window.location.href = "/dashboard"; // o cualquier ruta de inicio válida
                            }
                        } catch (e) {
                            // Si prevPage no es una URL válida, redirecciona de forma segura
                            window.location.href = "/dashboard";
                        }
                    } else {
                        // No hay página anterior, usa history.back() como fallback seguro
                        window.location.href = routes.API_BASE_URL
                    }
                } else {
                    alert("Error: " + data.message); // Muestra el mensaje de error
                }
            })
            .catch(error => console.error("Error en el inicio de sesión:", error));
    }
}


document.addEventListener("DOMContentLoaded", () => AuthClass.init())

