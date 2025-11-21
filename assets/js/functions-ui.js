import routes from "./routes.js"
import classMenu from "./classMenu.js"

export default class FunctionUi {

    constructor(idModule, idFunction, functionGetTable, handlerGetCatalog = null, modalParent) {

        this.classMenu = new classMenu(this, idModule, idFunction)

        this.initEventsUi()
        this.registerHandlebarsHelpers()
        this.filtersSelectId = {}
        this.handlerGetData = functionGetTable
        this.handlerGetCatalog = handlerGetCatalog
        this.isPopulatingForm = false
        this.modalParentCall = modalParent

    }

    registerHandlebarsHelpers() {
        Handlebars.registerHelper('eq', function (a, b) {
            return a == b
        })
        Handlebars.registerHelper('noeq', function (a, b) {
            return a != b
        })
        Handlebars.registerHelper('state', function (state) {

            const stateClassMap = {
                Activa: 'columnActive',
                Expirada: 'columnExpired',
                Autorizada: 'columnAutorized',
                Cancelada: 'columnCancel',
            };

            let stateClass = stateClassMap[state] || '';

            return stateClass
        })
        Handlebars.registerHelper('imagePath', function (filename, type) {
            return `${routes.API_BASE_URL}api/images/catalogs?name=${filename}&type=${type}`
        })
    }

    initEventsUi() {

        $('.js-example-basic-single').each(function () {
            const modal = $(this).closest('.modal');

            $(this).select2({
                placeholder: 'Seleccione una opción',
                allowClear: true,
                dropdownParent: modal.length ? modal : $(document.body)
            })
        })

        const tbody = document.querySelectorAll("table > tbody");

        tbody.forEach(table => {
            table.addEventListener("click", function (e) {
                const tr = e.target.closest("tr");

                if (tr) {

                    const type = table.dataset.type ?? "single"

                    if (type === "single") {

                        const rows = table.querySelectorAll("tr")

                        if (rows) {
                            rows.forEach(element => {
                                element.classList.remove("activeTr")
                            })
                        }
                    }

                    tr.classList.toggle("activeTr")
                }
            })
            table.addEventListener("dblclick", (e) => {
                if (e.target.classList.contains("editableColumn")) {
                    this.createInputColumn(e.target)
                }
            })
        })

        const btnFilter = document.querySelector(".btnFiltersPanel") ?? null
        const btnModalConfirm = document.querySelector(".btnModalConfirm") ?? null
        const btnExport = document.querySelector(".btnExportData") ?? null
        const inputSearList = document.querySelectorAll(".inputSearchPanel") ?? null
        const elementPanel = document.querySelectorAll(".contentListElements") ?? null
        const btnCloseModalAlert = document.getElementById("btnCloseModalAlert") ?? null

        if (btnCloseModalAlert) {

            btnCloseModalAlert.addEventListener("click", () => {

                const modalInstance = this.initModalInstance("modalAlertPage")

                if (modalInstance) {
                    modalInstance.hide()
                }

            })
        }

        if (btnFilter) {

            btnFilter.addEventListener("click", () => {
                const filterPanel = document.getElementById("spaceFilters")
                filterPanel.style.display = "block"
            })

            document.querySelector(".closeFilter").addEventListener("click", () => {
                const filterPanel = document.getElementById("spaceFilters")
                filterPanel.style.display = "none"
            })

            document.getElementById("contentFilters").addEventListener("click", async (e) => {
                if (e.target.matches("input[type=checkbox]")) {
                    await this.addFiltersObject(e)
                }
                if (e.target.matches(".searchIcon")) {
                    const container = e.target.parentNode

                    if (!container.querySelector("input")) {
                        const inputSearch = document.createElement("input")
                        inputSearch.type = "text"
                        inputSearch.className = "inputSearchFilter"
                        inputSearch.name = "searchFilter"
                        inputSearch.placeholder = "Buscar"

                        const contentDataHeader = container.closest('.elementFilterHeaderComplete')?.querySelector('.contentDataHeader')
                        inputSearch.dataset.type = contentDataHeader?.dataset.type || ''

                        container.append(inputSearch)

                        inputSearch.addEventListener("blur", (e) => {
                            if (!e.target.value.trim()) {
                                e.target.remove()
                            }
                        })

                        inputSearch.focus()
                    }
                }

            })

            document.getElementById("contentFilters").addEventListener("change", async (e) => {
                if (e.target.matches(".inputSearchFilter")) {

                    const containerFilter = e.target.closest(".elementFilterHeaderComplete")
                    if (!containerFilter) { return }
                    const containerOptions = containerFilter.querySelector(".contentDataHeader")
                    if (!containerOptions) { return }

                    this.searchValues(containerOptions, e.target.value)

                }
            })

            document.getElementById("btnFilterApply").addEventListener("click", async (e) => {
                this.handlerGetData()
            })
            document.getElementById("btnFilterClear").addEventListener("click", async (e) => {
                this.cleanerFiltersApply()
            })
        }

        if (btnExport) {
            btnExport.addEventListener("click", async (e) => {

                const btnId = e.target.parentNode.dataset.value
                const filters = await this.getFilters()
                const url = `${routes.API_BASE_URL}api/exports/docs?request=${btnId}&filters=${filters}`

                window.location.href = url

            })

        }

        if (inputSearList) {
            inputSearList.forEach(element => {
                element.addEventListener("change", (e) => {

                    const containerPanel = e.target.closest(".contentPanelList")
                    const type = e.target.dataset.type ?? null

                    if (!containerPanel) { return }

                    this.searchValues(containerPanel, e.target.value, type)
                })
            })
        }

        if (elementPanel) {
            elementPanel.forEach(panel => {
                panel.addEventListener("click", (e) => {
                    if (e.target.matches("li")) {

                        const typeElement = panel.dataset.type ?? "single"

                        if (typeElement === "single") {

                            panel.querySelectorAll("li").forEach(element => {
                                element.classList.remove("liActive")
                            })

                            e.target.classList.add("liActive")
                        }
                        if (typeElement === "multiple") {
                            e.target.classList.toggle("liActive")
                        }
                    }
                })
            })

        }

        if (btnModalConfirm) {

            btnModalConfirm.addEventListener("click", async () => {

            })
        }

    }

    showLoader() {
        const containerLoader = document.getElementById("pageLoader") ?? null
        if (containerLoader) {
            containerLoader.style.display = "flex"
        }
    }

    hideLoader() {
        const containerLoader = document.getElementById("pageLoader") ?? null
        if (containerLoader) {
            containerLoader.style.display = "none"
        }
    }

    viewAlertModal(type = "error", message = "", element) {

        const rowAlert = document.getElementById(element)

        if (!rowAlert) return

        // Limpiar clases anteriores
        rowAlert.classList.remove("alert-success", "alert-danger")

        // Aplicar clase según el tipo
        if (type === "ok") {
            rowAlert.classList.add("alert-success")
        } else {
            rowAlert.classList.add("alert-danger")
        }

        rowAlert.textContent = message
        rowAlert.style.display = "block"

        setTimeout(() => {
            rowAlert.style.display = "none"
        }, 5000)
    }

    async validateSession() {
        const url = `${API_BASE_URL}api/auth/session`
        const formData = new FormData()
        const fetchResponse = await this.sendFetchGet(url, "POST", formData)

        if (!fetchResponse?.result) {
            console.warn("Sesión inválida, redirigiendo...")
            window.location.href = `${routes.API_BASE_URL}login`
        } else {
            return true
        }
    }

    async sendFetchGet(url = "", method = "GET", form = null) {

        sessionStorage.setItem('prevPage', window.location.href)

        const isFormData = form instanceof FormData

        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    ...(isFormData ? {} : { "Content-Type": "application/json" })
                },
                body: method === "POST" ? (isFormData ? form : JSON.stringify(form)) : null
            })

            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`)
            }

            return await response.json()
        } catch (error) {
            console.error("Error en la petición:", error)
            return null
        }
    }

    sendFetchBlob = async (url = "", method = "GET", form = null) => {
        sessionStorage.setItem('prevPage', window.location.href);

        const isFormData = form instanceof FormData;

        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    ...(isFormData ? {} : {
                        "Content-Type": "application/json",
                        "Accept": "application/pdf"  // 🔹 solo PDF
                    })
                },
                body: method === "POST" ? (isFormData ? form : JSON.stringify(form)) : null
            });

            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }

            const blob = await response.blob();
            return blob;

        } catch (error) {
            console.error("Error en la petición PDF:", error);
            return null;
        }
    };

    createOptionsSelect(options = [], select) {

        if (!select || (!(select instanceof HTMLSelectElement) && !(select.tagName === "DATALIST"))) {
            return
        }

        // Limpiar opciones previas
        select.innerHTML = ""

        // Crear un fragmento para optimizar la inserción de elementos
        const fragment = document.createDocumentFragment()

        // Agregar una opción por defecto
        const placeholder = document.createElement("option")
        placeholder.value = ""
        placeholder.textContent = "Selecciona una opción"
        placeholder.classList.add("optionPlaceHolder")
        fragment.appendChild(placeholder)

        // Agregar las opciones dinámicas
        options.forEach(option => {
            const optionElement = document.createElement("option")
            optionElement.value = option.id
            optionElement.textContent = option.description.toLowerCase().replace(/\b\w/g, letra => letra.toUpperCase())
            fragment.appendChild(optionElement)
        })

        // Insertar el fragmento en el select
        select.appendChild(fragment)
    }

    createOptionsList(options = [], ul, type = 'single') {
        if (!ul || !(ul instanceof HTMLUListElement)) {
            console.error("El elemento lista no es válido")
            return
        }

        ul.innerHTML = ""

        const fragment = document.createDocumentFragment()

        options.forEach(option => {
            const liElement = document.createElement("li")
            liElement.className = "li-options"
            liElement.value = option.id
            liElement.dataset.uuid = option.id
            liElement.dataset.type = type
            liElement.textContent = option.description

            // Agregar todas las propiedades extra como data-atributos
            for (const [key, value] of Object.entries(option)) {
                if (key !== 'id' && key !== 'description') {
                    liElement.dataset[key] = value
                }
            }

            // También puedes guardar la descripción como dataset si quieres
            liElement.dataset.content = option.description

            fragment.appendChild(liElement)
        })

        ul.appendChild(fragment)
    }

    showAlert(message, type = 'success') {

        const alertBox = $('#alertBox')

        // Limpiar clases previas
        alertBox.removeClass('d-none alert-success alert-danger alert-warning alert-info')

        // Agregar clase correspondiente
        const typeClass = {
            success: 'alert-success',
            error: 'alert-danger',
            warning: 'alert-warning',
            info: 'alert-info'
        }

        alertBox
            .addClass(typeClass[type] || 'alert-info')
            .html(message)
            .fadeIn()

        // Mostrar el modal si no está visible
        this.deleteblurElement()
        const modal = new bootstrap.Modal(document.getElementById('modalAlertPage'))

        modal.show()

        setTimeout(function () {

            modal.hide()
        }, 5000)

    }

    createDocContainer(file, container, id = 0) {

        const name = file.name ?? ''

        const source = document.getElementById("template-documents").innerHTML
        const template = Handlebars.compile(source)
        const data = {
            name: name,
            id: id,
        }
        const html = template(data)
        container.insertAdjacentHTML('beforeend', html)
    }

    viewErrorsInput(errors = null, form = "formData") {

        if (typeof errors === "object") {
            // Elimina errores previos

            const errorsPreview = document.getElementById(form).querySelectorAll('.error-messages')

            if (errorsPreview.length > 0) {
                errorsPreview.forEach(e => e.remove())
            }

            Object.entries(errors).forEach(([campo, mensajes]) => {
                const input = document.getElementById(form).querySelector(`[name="${campo}"]`)
                if (input) {
                    // Asegura que tenga ID
                    if (!input.id) input.id = `input_${campo}`

                    // Elimina mensaje anterior si existe
                    const siguiente = input.nextElementSibling
                    if (siguiente && siguiente.classList.contains('error-messages')) {
                        siguiente.remove()
                    }

                    // Crear div contenedor de errores
                    const divErrores = document.createElement('div')
                    divErrores.className = 'error-messages'

                    // Agrega cada mensaje
                    mensajes.forEach(msg => {
                        const p = document.createElement('p')
                        p.className = 'error-text'
                        p.textContent = msg
                        divErrores.appendChild(p)
                    })

                    // Insertar después del input/select
                    if (input.classList.contains('js-example-basic-single') && $(input).hasClass('select2-hidden-accessible')) {
                        // Si es un select2, inserta el error después del contenedor select2
                        const container = $(input).next('.select2') // div que select2 crea
                        if (container.length) {
                            container.after(divErrores)
                        } else {
                            input.parentNode.insertBefore(divErrores, input.nextSibling)
                        }
                    } else {
                        input.parentNode.insertBefore(divErrores, input.nextSibling)
                    }
                }
            })
        }

    }

    fillForm = async (selector, data, dependency = {}, catalogs = {}) => {
        this.isPopulatingForm = true;
        const $form = $(selector);

        // 1️⃣ Llena los campos simples primero
        for (const [key, value] of Object.entries(data)) {
            if (Object.keys(catalogs).includes(key)) continue;
            const $field = $form.find(`[name='${key}']`);
            if ($field.length) this.setFieldValue($field, value);
        }

        // 2️⃣ Luego maneja los selects dependientes
        for (const [key, config] of Object.entries(catalogs)) {
            const $input = $form.find(`[name='${key}']`);
            const selectedValue = data[key];

            // Si tiene dependencia definida, espera a que se cargue
            if (config.dependency && dependency[config.dependency]) {
                const parentValue = data[config.dependency];
                await this.handlerGetCatalog(config.endpoint, parentValue);
            } else if (config.endpoint) {
                await this.handlerGetCatalog(config.endpoint);
            }

            // Ahora asigna el valor y actualiza select2
            this.setFieldValue($input, selectedValue);
        }

        this.isPopulatingForm = false;
        return true;
    };

    setFieldValue($field, value) {
        const type = $field.attr('type');
        const tag = $field.prop('tagName').toLowerCase();

        if (type === 'checkbox') {
            $field.prop('checked', !!value).trigger('change');
        } else if (type === 'radio') {
            $field.filter(function () {
                return this.value == value;
            }).prop('checked', true).trigger('change');
        } else if (tag === 'select' && $field.hasClass('select2-hidden-accessible')) {
            $field.val(value).trigger('change.select2');
        } else {
            $field.val(value).trigger('change');
        }
    }

    createInputColumn(target) {

        const value = target.dataset.value ?? 1;
        const type = target.dataset.type ?? "number";

        const input = document.createElement("input");
        input.setAttribute("type", type);
        input.value = value;
        input.classList = "inputTempColumn";

        // Limpia el contenido anterior y agrega el input
        target.replaceChildren(input);

        input.focus()

    }

    getFilters = async () => {

        let inputSearch = $("#inputSearch").val()

        if (inputSearch && inputSearch.trim() !== "") {
            this.filtersSelectId['search'] = inputSearch.trim()
        } else {
            delete this.filtersSelectId['search']
        }

        const stringFilters = JSON.stringify(this.filtersSelectId)
        return stringFilters

    }

    addFiltersObject = async (element) => {

        const type = element.target.closest(".contentDataHeader").dataset.type
        const id = element.target.closest(".filterData").dataset.id

        if (!this.filtersSelectId.hasOwnProperty(type)) {
            this.filtersSelectId[type] = []
        }
        if (!element.target.checked) {

            this.filtersSelectId[type] = this.filtersSelectId[type].filter(item => item !== id)

            if (this.filtersSelectId[type].length === 0) delete this.filtersSelectId[type]

        } else {

            this.filtersSelectId[type].push(id)
        }

        return this.filtersSelectId
    }

    createFilters = async (filters) => {

        fetch(`${routes.API_BASE_URL}assets/html/plantillas/filters-template.hbs`)
            .then(response => response.text())
            .then(templateSource => {
                const template = Handlebars.compile(templateSource)

                // Compila y renderiza el template
                const html = template({ filters })
                document.querySelector('#contentFilters').innerHTML = html
            })


        return true

    }

    searchValues(container, value, type = "checkbox") {

        if (!value) {
            Array.from(this.getSearchableElements(container, type)).forEach(child => {
                child.style.display = '';
            });
            return;
        }

        const elements = this.getSearchableElements(container, type);

        elements.forEach(element => {

            const textContent = element.dataset.content ?? '';
            const match = textContent.toLowerCase().includes(value.toLowerCase());
            element.style.display = match ? '' : 'none';

        });
    }

    getSearchableElements(container, type = 'checkbox') {
        if (!container) return [];

        // Automáticamente detecta los elementos según el tipo o por estructura
        if (type === 'list' || container.querySelector === 'UL' || container.tagName === 'OL') {
            return Array.from(container.querySelectorAll('li'))
        }

        if (type === 'table' || container.tagName === 'TABLE') {
            const tbody = container.querySelector('tbody');
            return tbody ? Array.from(tbody.rows) : Array.from(container.rows); // <tr>
        }

        if (type === 'checkbox') {
            return Array.from(container.querySelectorAll(".elementCatalog"))
        }

        return
    }

    initModalInstance(id) {

        const modalElement = document.getElementById(id)
        const modalInstance = bootstrap.Modal.getInstance(modalElement)
        const modal = modalInstance || new bootstrap.Modal(modalElement)
        this.deleteblurElement()
        return modal
    }

    deleteblurElement() {
        const buttonElement = document.activeElement
        buttonElement.blur()
    }

    cleanerFiltersApply() {

        const elementsFilters = document.getElementById("contentFilters")

        if (!elementsFilters) {
            return
        }

        const inputs = elementsFilters.querySelectorAll("input[type='checkbox']")

        if (!inputs) {
            return
        }

        inputs.forEach(element => {
            element.checked = false
        })

        this.filtersSelectId = {}

        this.handlerGetData()
    }

}
