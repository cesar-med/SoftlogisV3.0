<div class="modal fade" id="modalEmployeeForm" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class='modal-dialog modal-lg'>
        <div class='modal-content'>
            <div class='modal-body'>
                <form method="post" enctype="multipart/form-data" id='employeeForm'>
                    <div class="contentFilters__title">
                        <div class="title_name">Información del empleado</div>
                        <div class="close_name closeModal" id="btnCloseEmployeeModal">X</div>
                    </div>
                    <div class="alert alert-danger text-center rowMessage" role="alert" id="employeeAlerts"></div>
                    <br>
                    <div class="row">
                        <div class="col-sm-12 col-md-6 col-lg-6">
                            <div class='form-group'>
                                <label for="first_name" class="required">Nombres</label>
                                <input type='text' class='form-control' name='first_name' value="">
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-6 col-lg-6">
                            <div class='form-group'>
                                <label for="last_name" class="required">Apellidos</label>
                                <input type='text' class='form-control' name='last_name' value="">
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-6 col-lg-6">
                            <div class='form-group'>
                                <label for="address">Domicilio</label>
                                <input type='text' class='form-control' name='address' value="">
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-6 col-lg-6">
                            <div class='form-group'>
                                <label for="locality">Colonia</label>
                                <input type='text' class='form-control' name='locality' value="">
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-6 col-lg-6">
                            <div class='form-group'>
                                <label for="city">Ciudad</label>
                                <input type='text' class='form-control' name='city' value="">
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-6 col-lg-6">
                            <div class='form-group'>
                                <label for="state">Estado</label>
                                <input type='text' class='form-control' name='state' value="">
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