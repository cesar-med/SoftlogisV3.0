import EmployeeClass from "./EmployeeClass.js";

const employeeClass = new EmployeeClass()

document.addEventListener("DOMContentLoaded", async function () {

    employeeClass.loadInitialData()
    initEvents()

})

function initEvents() {

    const btnCloseEmployeeModal = document.getElementById("btnCloseEmployeeModal")
    const btnSendFetch = document.getElementById("btnSendFetch")
    const inputSearch = document.getElementById("inputSearch")
    const countPerPage = document.getElementById("countPerPage")

    if (btnCloseEmployeeModal) {
        btnCloseEmployeeModal.addEventListener("click", function () {
            employeeClass.closeModal()
        })
    }

    if (btnSendFetch) {
        btnSendFetch.addEventListener("click", function () {
            employeeClass.sendRequestFetch()
        })
    }

    if (inputSearch) {
        inputSearch.addEventListener("change", function (e) {

            employeeClass.getEmployees(1)
        })
    }

    if (countPerPage) {
        countPerPage.addEventListener("change", function (e) {
            employeeClass.changecountPerPage(e.target.value)
        })
    }

    document.querySelector(".btnNewRegister").addEventListener("click", function () {

        employeeClass.openModalInsert()

    })

    document.getElementById("employeesContainerData").addEventListener("click", function (e) {
        if (e.target.classList.contains("btnEditRegister")) {

            employeeClass.openModalUpdate(e.target.parentNode.value)
        }
    })

}