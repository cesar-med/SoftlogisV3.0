import routes from "../../../assets/js/routes.js"
import functionUi from "../../../assets/js/functions-ui.js"

import Paginator from "../../../assets/js/paginator.js";
import { renderWithTemplate } from "../../../assets/js/rendererTables.js";
import { createPagination } from "../../../assets/js/pagination.js";



export default class EmployeeClass {

    constructor() {
        this.uuid = ""
        this.request = "insert"
        this.functionsUi = new functionUi("Recursos humanos", 100, this.getEmployees, this.getCatalogsForm)
        this.page = 1
        this.countPerPage = 50
        this.paginator = new Paginator();
    }

    loadInitialData = async () => {

        try {
            const response = await fetch(`${routes.API_BASE_URL}api/employee/loader?page=1&countPerPage=${this.countPerPage}`); // tu endpoint
            const data = await response.json();

            if (!data.employees.result) {
                this.functionsUi.showAlert("No hay registros para mostrar", 'error')
                return
            }

            const filters = data.filters
            const employees = data.employees.data
            const registers = data.registers

            this.paginator.setData(employees, registers, this.page, this.countPerPage);

            renderWithTemplate("employeesContainerData", "user-table-template", employees);
            createPagination(".sectionPagesCtrl", this.paginator, this.getEmployees);
            await this.functionsUi.createFilters(filters);

            this.functionsUi.hideLoader()

        } catch (err) {
            console.error("Error cargando usuarios:", err);
        }
        $(".loaderPage").hide()

    }

    openModalUpdate = async (uuid = null) => {

        this.functionsUi.showLoader()

        if (!uuid) {
            this.functionsUi.showAlert("Error al obtener el id de la orden", 'error')
            return
        }

        const url = `${routes.API_BASE_URL}api/employee/getUser?uuid=${uuid}`

        const user = await this.functionsUi.sendFetchGet(url, "GET")

        if (!user.result) {
            this.functionsUi.showAlert(user.message, "error")
            return
        }

        const objCatalogs = {
            role_id: { endpoint: 'roles' },
            employee_id: { endpoint: 'employees' }
        };

        const dependencies = {};
        await this.functionsUi.fillForm("#userForm", user.data[0], dependencies, objCatalogs)
        this.uuid = uuid
        this.request = 'update'

        await this.openModal()

        this.functionsUi.hideLoader()
    }

    openModalInsert = async () => {
        this.uuid = ""
        this.request = 'insert'

        await this.getCatalogsForm()
        this.openModal()
    }

    openModal = async () => {

        const modal = this.functionsUi.initModalInstance("modalUserForm")
        modal.show()

        return true

    }

    closeModal = async () => {

        this.cleanForm()
        const modal = this.functionsUi.initModalInstance("modalUserForm")
        modal.hide()
    }

    cleanForm() {

        const form = document.getElementById("userForm")

        if (form) {
            $('#userForm')[0].reset()
            form.querySelectorAll(".error-text").forEach(select => {
                select.remove()
            })
            form.querySelectorAll("select").forEach(select => {
                select.value = ""
                $(select).trigger("change")
            })
        }
    }

    getEmployees = async (page = 1) => {

        this.functionsUi.showLoader()
        const filters = await this.functionsUi.getFilters()
        const url = `${routes.API_BASE_URL}api/employee/employees?filters=${filters}&page=${page}&countPerPage=${this.countPerPage}`

        const response = await this.functionsUi.sendFetchGet(url, "GET")

        if (response) {

            const employees = response.employees.data
            const registers = response.registers

            this.paginator.setData(employees, registers, page, this.countPerPage);

            renderWithTemplate("employeesContainerData", "user-table-template", employees);
            createPagination(".sectionPagesCtrl", this.paginator, this.getEmployees, page);

        }

        this.functionsUi.hideLoader()

    }

    getCatalogsForm = async (catalog = "all", params = "", flag = true) => {

        const url = `${routes.API_BASE_URL}api/employee/catalogs?type=${catalog}&param=${params}`

        const response = await this.functionsUi.sendFetchGet(url, "GET")

        if (response) {
            if (catalog === "employee_types") {
                this.functionsUi.createOptionsSelect(response.employees.data, document.getElementById("employee_id"))
            }
            if (catalog === "work_shifts") {
                this.functionsUi.createOptionsSelect(response.roles.data, document.getElementById("role_id"))
            }
            if (catalog === "offices") {
                this.functionsUi.createOptionsSelect(response.roles.data, document.getElementById("role_id"))
            }
            if (catalog === "departments") {
                this.functionsUi.createOptionsSelect(response.roles.data, document.getElementById("role_id"))
            }
            if (catalog === "positions") {
                this.functionsUi.createOptionsSelect(response.roles.data, document.getElementById("role_id"))
            }
            if (catalog === "contract_types") {
                this.functionsUi.createOptionsSelect(response.roles.data, document.getElementById("role_id"))
            }

            if (catalog === 'all') {
                if (response.employees) {
                    this.functionsUi.createOptionsSelect(response.employees.data, document.getElementById("employee_id"))
                }
                if (response.roles) {
                    this.functionsUi.createOptionsSelect(response.roles.data, document.getElementById("role_id"))
                }
            }
            return response
        }

        return true

    }

    cleanRowMessage() {

        const rowAlerts = document.getElementById("userAlerts")
        rowAlerts.innerText = ""
        rowAlerts.style.display = "none"
    }

    sendRequestFetch = async () => {

        const url = this.request === 'insert' ? `${routes.API_BASE_URL}api/employee/insert` : `${routes.API_BASE_URL}api/employee/update`

        const form = document.getElementById("userForm")
        if (!form) {
            return
        }

        const userForm = new FormData(form)
        userForm.append('uuid', this.uuid)

        const responseFetch = await this.functionsUi.sendFetchGet(url, 'POST', userForm)

        if (!responseFetch.result) {
            if (typeof responseFetch.message === 'object') {
                this.functionsUi.viewErrorsInput(responseFetch.message, "userForm")
            } else if (typeof message === 'string') {
                this.viewAlertModal("error", responseFetch.message, "userAlerts")
            }
        } else {

            if (this.request === 'insert') {
                this.viewAlertModal("ok", responseFetch.message, "userAlerts")
                this.cleanForm()
            } else if (this.request === 'update') {
                this.closeModal()
                this.functionsUi.showAlert(responseFetch.message, "ok")
            }

            this.getEmployees()

        }
    }

    viewAlertModal(type = "error", message = "", element = "userAlerts") {

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

    changecountPerPage(value) {

        this.countPerPage = value
        this.getEmployees()
    }

}