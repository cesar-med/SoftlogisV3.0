<div class="modal fade" id="modalUserForm" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class='modal-dialog modal-lg'>
        <div class='modal-content'>
            <div class='modal-body'>
                <form method="post" enctype="multipart/form-data" id='userForm'>
                    <div class="contentFilters__title">
                        <div class="title_name">Información usuario</div>
                        <div class="close_name closeModal" id="btnCloseUserModal">X</div>
                    </div>
                    <div class="alert alert-danger text-center rowMessage" role="alert" id="userAlerts"></div>
                    <br>
                    <div class="row">
                        <div class="col-sm-12 col-md-6 col-lg-6">
                            <div class='form-group'>
                                <label for="employee_id" class="required">Personal</label>
                                <select class="js-example-basic-single" name="employee_id" id="employee_id" style="width: 100%">
                                </select>
                            </div>
                        </div>
                        <div class='col-sm-12 col-md-6 col-lg-6'>
                            <div class='form-group'>
                                <label for="role_id" class="required">Rol</label>
                                <select class="js-example-basic-single" name="role_id" id="role_id" style="width: 100%">
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-6 col-lg-6">
                            <div class='form-group'>
                                <label for="email" class="required">Correo</label>
                                <input type='text' class='form-control' name='email' value="">
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-6 col-lg-3">
                            <div class='form-group'>
                                <label for="phone_number">teléfono</label>
                                <input type='text' class='form-control' name='phone_number' value="">
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-6 col-lg-3">
                            <div class='form-group'>
                                <label for="password" class="required">Contraseña</label>
                                <input type='password' class='form-control' name='password' value="">
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-6 col-lg-3">
                            <div class='form-group'>
                                <label for="password_confirm" class="required">Validar contraseña</label>
                                <input type='password' class='form-control' name='password_confirm' value="">
                            </div>
                        </div>
                        <div class='col-sm-12 col-md-6 col-lg-3'>
                            <div class='form-group'>
                                <div class="form-group">
                                    <label for="status">Estatus</label><br>
                                    <label class="switch">
                                        <input type="checkbox" name="status" id="status" class="checkTypeSwitch" value="1" checked><span class="slider"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-12 text-right">
                            <button type='button' class='btnStyleModal' id="btnSendFetch" value=''>Aceptar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>