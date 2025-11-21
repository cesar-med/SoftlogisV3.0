<?php

include_once __DIR__ . "/../../../auth/controller/sessions.php";
include_once __DIR__ . '/../../../assets/html/plantillas/headers-html.php';

?>

<script type="module" src="./../admin/users/interface/UserEvents.js"></script>

<body>
    <?php include_once __DIR__ . '/../../../assets/html/plantillas/navbar.php'; ?>
    <?php include_once __DIR__ . '/../../../assets/html/plantillas/loader-page.html'; ?>
    <div class="contentGenerally">
        <div class="rowHeadersPage">
            <div class="contentImagePage">
                <div class="textPageTitle">Usuarios</div>
            </div>
            <div class="contentFunctionPage">
                <input type='text' id="inputSearch" placeholder='Buscar' value="">
                <button type="button" class="btnFunctionPage" title="Nuevo usuario">
                    <i class="fa-solid fa-circle-plus btnNewRegister"></i>
                </button>
                <button type="button" class="btnFunctionPage" title="Exportar usuarios" data-value="users">
                    <i class="fa-solid fa-cloud-arrow-down btnExportData"></i>
                </button>
                <button type="button" class="btnFunctionPage" title="Mostrar filtros">
                    <i class="fa-solid fa-filter btnFiltersPanel"></i>
                </button>
            </div>
        </div>
        <div class="contentBodyPage" id="usersContainerData">
        </div>
        <div class="contengePagination">
            <div class="sectionPageInfo">
                <p>Total: <span id="countResult"></span><br>Mostrando: <span id="spanInitResult">1</span> al <span
                        id="spanFinishResult">15</span></p>
            </div>
            <div class="pagination-settings">
                <label for="countPerPage" class="pagination-label">Mostrar:</label>
                <select name="countPerPage" id="countPerPage" class="pagination-select">
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="50" selected>50</option>
                    <option value="100">100</option>
                    <option value="250">250</option>
                </select>
                <span class="pagination-label">por página</span>
            </div>
            <div class="sectionPagesCtrl" data-table="dataViewGen">

            </div>
        </div>
    </div>

    <div class="spaceFilters">
        <?php include_once "filters.php" ?>
    </div>
    <?php include_once "modal-form.php" ?>
    <?php include_once "template-render.html" ?>
    <?php include_once __DIR__ . '/../../../assets/html/plantillas/modal-alert.php'; ?>

</body>

</html>