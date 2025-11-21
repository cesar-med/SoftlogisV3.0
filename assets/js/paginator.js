// paginator.js
export default class Paginator {
    constructor(countPerPage = 250) {
        this.countPerPage = countPerPage;
        this.totalRecords = 0;
        this.currentPage = 1;
        this.data = [];
    }

    setData(data, registers, currentPage, countPerPage) {
        this.data = data;
        this.totalRecords = registers;
        this.countPerPage = countPerPage
        this.currentPage = currentPage
    }

    getPage(page = 1) {
        this.currentPage = page;
        const start = (page - 1) * this.countPerPage;
        const end = page * this.countPerPage;
        return this.data.slice(start, end);
    }

    totalPages() {
        return Math.ceil(this.totalRecords / this.countPerPage);
    }

    totalRegisters() {
        return this.totalRecords;
    }

    getCurrentPage() {
        return this.currentPage;
    }

    getCountPerPage() {
        return this.countPerPage;
    }

    setCurrentPage(currentPage) {
        this.currentPage = currentPage
    }

}
