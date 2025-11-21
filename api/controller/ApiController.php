<?php

require_once __DIR__ . "/../../helpers/php/DocumentsCreateHelper.php";
class ApiController
{
    public function exportDataXlsx($request)
    {

        if (empty($request['request'])) {
            http_response_code(400);
            echo json_encode(['result' => false, 'message' => 'Parámetro "request" es obligatorio']);
            exit;
        }

        switch ($request['request']) {
            case 'users':
                require_once __DIR__ . "/../../admin/users/controller/UserController.php";
                $_userController = new UserController();
                $userList = $_userController->usersListExport($request);

                if (!$userList['result'] || empty($userList['data'])) {
                    http_response_code(404);
                    echo json_encode(['result' => false, 'message' => 'No hay datos para exportar']);
                    exit;
                }

                DocumentsCreateHelper::createXlsxDownload($userList['data'], 'reporte_usuarios.xlsx');
                exit;
        }
    }
}
