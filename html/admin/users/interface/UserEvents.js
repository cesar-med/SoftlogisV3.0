import UserClass from "./../interface/UserClass.js";

const userClass = new UserClass()

document.addEventListener("DOMContentLoaded", async function () {

    userClass.loadInitialData()
    initEvents()

})

function initEvents() {

    const btnCloseUserModal = document.getElementById("btnCloseUserModal")
    const btnSendFetch = document.getElementById("btnSendFetch")
    const inputSearch = document.getElementById("inputSearch")
    const countPerPage = document.getElementById("countPerPage")

    if (btnCloseUserModal) {
        btnCloseUserModal.addEventListener("click", function () {
            userClass.closeModal()
        })
    }

    if (btnSendFetch) {
        btnSendFetch.addEventListener("click", function () {
            userClass.sendRequestFetch()
        })
    }

    if (inputSearch) {
        inputSearch.addEventListener("change", function (e) {

            userClass.getUsers(1)
        })
    }

    if (countPerPage) {
        countPerPage.addEventListener("change", function (e) {
            userClass.changecountPerPage(e.target.value)
        })
    }

    document.querySelector(".btnNewRegister").addEventListener("click", function () {

        userClass.openModalInsert()

    })

    document.getElementById("usersContainerData").addEventListener("click", function (e) {
        if (e.target.classList.contains("btnEditRegister")) {

            userClass.openModalUpdate(e.target.parentNode.value)
        }
    })

}