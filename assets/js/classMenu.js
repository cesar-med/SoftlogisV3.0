import routes from "./routes.js"

export default class Sidenav {
    constructor(parentInstance, idModule, idFunction) {

        this.parent = parentInstance
        this.initEvents()
        this.getModulesUser()
        this.idModule = idModule
        this.idFunction = idFunction

    }

    initEvents() {

        Handlebars.registerHelper("styleIfHidden", function (hasActiveFunction) {
            return hasActiveFunction ? "" : "display: none;";
        });
        // Usamos delegación de eventos para el menú de módulos
        document.querySelector(".sidenav")?.addEventListener("click", event => {
            // Verificamos si el elemento clickeado es un '.space_menu'
            if (event.target.closest(".space_menu")) {
                this.controllerClickedSidenav();
            }
            // Verificamos si el clic es sobre un '.modulesName'
            if (event.target.closest(".modulesName")) {
                this.clickedModuleMenu(event, event.target.closest(".sectionModulesMenu"));
            }
        });

        // Usamos delegación de eventos para los botones de cerrar sesión
        document.querySelector(".sidenav")?.addEventListener("click", event => {
            if (event.target.closest(".sectionCloseSession")) {
                window.location.href = routes.LOGOUT;
            }
        });

    }

    clickedModuleMenu(event, section) {
        const target = event.target.closest(".modulesName");
        if (!target || !section.contains(target)) return;

        // Ocultar todos los contentFunctionModule
        document.querySelectorAll(".modulesName .contentFunctionModule").forEach(content => {
            content.style.display = "none";
        });

        // Mostrar el contenido del módulo clickeado
        const contentToShow = target.querySelector(".contentFunctionModule");
        if (contentToShow) {
            contentToShow.style.display = "block";
        }

        setTimeout(() => {
            target.scrollIntoView({ behavior: "smooth" });
        }, 150);
    }

    controllerClickedSidenav() {
        const spaceMenu = document.querySelector(".space_menu");
        const sidenav = document.querySelector(".sidenav");

        if (spaceMenu.classList.contains("activeMenu")) {
            sidenav.style.display = "none";
            spaceMenu.classList.remove("activeMenu");
        } else {
            sidenav.style.display = "";
            spaceMenu.classList.add("activeMenu");
        }
    }

    getModulesUser = async () => {

        const permissions = await this.parent.sendFetchGet(`${routes.API_BASE_URL}/api/modules/user`, "GET", null)

        if (!permissions.result) { }

        const createMenu = await this.createNavbarUser(permissions)
    }

    createNavbarUser = async (response) => {

        const permisos = response.permissions;
        const infoUser = response.info;

        $(".sectionUserInfo__name").html(infoUser.name);
        $(".sectionUserInfo__rol").html(infoUser.role);

        // Prepara los datos para el template
        for (let key in permisos) {
            if (permisos.hasOwnProperty(key)) {

                permisos[key].isActive = (key === this.idModule); // para el módulo
                permisos[key].hasActiveFunction = false;

                for (let i = 0; i < permisos[key].length; i++) {
                    const funcion = permisos[key][i];
                    funcion.path = `${routes.API_BASE_URL}${funcion.path}`
                    funcion.isFunctionActive = (funcion.id == this.idFunction);

                    if (funcion.isFunctionActive) {
                        permisos[key].hasActiveFunction = true;
                    }
                }
            }
        }

        fetch(`${routes.API_BASE_URL}assets/html/plantillas/modules-template.hbs`)
            .then(response => response.text())
            .then(templateSource => {
                const template = Handlebars.compile(templateSource);

                // Compila y renderiza el template
                const html = template({ permisos })
                document.querySelector('.sectionModulesMenu').innerHTML = html;
            });

    }
}
