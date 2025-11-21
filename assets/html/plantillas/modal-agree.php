<!-- Modal -->

<div class="modal fade" id="modalAgreeSub" tabindex="-1" aria-labelledby="modalAlertPageLabel" aria-hidden="true" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <form method="post" enctype="multipart/form-data" id='formDataAgree' data-type="">
                    <div class="contentFilters__title">
                        <div class="title_name" id="modalAgreeTitle"></div>
                        <div class="close_name closeModal" id="btnCloseModalAgree">
                            X</div>
                    </div>
                    <div class="alert alert-danger text-center rowMessage" role="alert" id="rowMessageAgree" style="display: none;"></div>
                    <br>
                    <label for="descriptionAgree">Descripción</label>
                    <input type="text" class="form-control" name="description" id="descriptionAgree" placeholder="añade una descripción breve">
                    <br>
                    <div class="row">
                        <div class="col-md-12 text-right">
                            <button type='button' class='btnStyleModal btnModalAgree' value=''>Aceptar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>