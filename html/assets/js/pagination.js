
export function createPagination(containerSelector, paginator, onPageClick) {

    const container = document.querySelector(containerSelector)
    container.innerHTML = ""
    const totalPages = paginator.totalPages()
    const initResult = (paginator.getCurrentPage() - 1) * paginator.getCountPerPage() + 1
    const endResult = Math.min(paginator.getCurrentPage() * paginator.getCountPerPage(), paginator.totalRegisters())
    // Mostrar el total de registros (si aplica)
    const countResultEl = document.getElementById("countResult")
    const countInitResult = document.getElementById("spanInitResult")
    const countFinishResult = document.getElementById("spanFinishResult")

    if (countResultEl) countResultEl.innerText = paginator.totalRegisters()
    if (countInitResult) countInitResult.innerText = initResult
    if (countFinishResult) countFinishResult.innerText = endResult

    // Botón "Anterior"
    const btnPrev = document.createElement("div")
    btnPrev.className = "pagesCtrl btnRes"
    btnPrev.innerHTML = '<i class="fa-solid fa-caret-left"></i>'
    btnPrev.onclick = () => {
        let currentPageTemp = paginator.getCurrentPage() - 1
        if (currentPageTemp >= 1) {
            onPageClick(currentPageTemp)
            paginator.setCurrentPage(currentPageTemp)
        }

    }

    if (paginator.getCurrentPage() === 1) btnPrev.classList.add("disabled")
    container.appendChild(btnPrev)

    // --- Paginación compacta ---
    const maxVisible = 5
    let start = Math.max(1, paginator.getCurrentPage() - Math.floor(maxVisible / 2))
    let end = Math.min(totalPages, start + maxVisible - 1)

    // Ajusta si estamos al final
    if (end - start < maxVisible - 1) {
        start = Math.max(1, end - maxVisible + 1)
    }

    // Si hay páginas anteriores no visibles, muestra "1 ..."
    if (start > 1) {
        container.appendChild(createPageButton(1, paginator.getCurrentPage(), onPageClick, container))
        if (start > 2) container.appendChild(createEllipsis())
    }

    // Botones visibles
    for (let i = start; i <= end; i++) {
        container.appendChild(createPageButton(i, paginator.getCurrentPage(), onPageClick, container))
    }

    // Si hay páginas posteriores no visibles, muestra "... totalPages"
    if (end < totalPages) {
        if (end < totalPages - 1) container.appendChild(createEllipsis())
        container.appendChild(createPageButton(totalPages, paginator.getCurrentPage(), onPageClick, container))
    }

    // Botón "Siguiente"
    const btnNext = document.createElement("div")
    btnNext.className = "pagesCtrl btnSum"
    btnNext.innerHTML = "<i class='fa-solid fa-caret-right'></i>"
    btnNext.onclick = () => {
        let currentPageTemp = paginator.getCurrentPage() + 1
        if (currentPageTemp <= totalPages) {
            onPageClick(currentPageTemp)
            paginator.setCurrentPage(currentPageTemp)
        }

    }

    if (paginator.getCurrentPage() === totalPages) btnNext.classList.add("disabled")
    container.appendChild(btnNext)
}

// --- Funciones auxiliares ---
function createPageButton(pageNumber, currentPage, onPageClick, container) {
    const btn = document.createElement("div")
    btn.className = "BtnPageCtl"
    btn.dataset.value = pageNumber
    btn.textContent = pageNumber

    if (pageNumber === currentPage) btn.classList.add("activePage")

    btn.onclick = () => {
        container.querySelectorAll(".BtnPageCtl").forEach(b => b.classList.remove("activePage"))
        btn.classList.add("activePage")
        onPageClick(pageNumber)
        currentPage = pageNumber
    }

    return btn
}

function createEllipsis() {
    const ellipsis = document.createElement("div")
    ellipsis.className = "ellipsis"
    ellipsis.textContent = "..."
    return ellipsis
}
