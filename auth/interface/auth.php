<?php
include_once __DIR__ . '/../../assets/html/plantillas/headers-html.php';

?>

<script type="module" src="./functions.js"></script>

<body>
    <div class="container-flex-login">
        <div class="flex-row m-5 text-center">
            <h3>Bienvenidos a Usa Mex Carrier</h3>
        </div>
        <div>
            <form id="loginForm" class="full-width">
                <div class="container-forms flex-column-center p-3 box-shadow">
                    <label for="" class="label-title-login top-position">Iniciar sesión</label>
                    <div class="contentInputUi full-width">
                        <div class="contentIcon iconUser"></div>
                        <div class="contentInput">
                            <input type="text" class="input" name="username" placeholder="Usuario">
                        </div>
                    </div>
                    <div class="contentInputUi full-width">
                        <div class="contentIcon iconPassword"></div>
                        <div class="contentInput">
                            <input type="password" class="input" name="password" placeholder="Password">
                        </div>
                    </div>
                    <button type="submit" class="btnFunctionPrimary full-width bottom-position">Iniciar sesión</button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>