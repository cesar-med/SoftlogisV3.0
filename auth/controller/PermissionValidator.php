<?php

require_once __DIR__ . '/../model/authModel.php';

class PermissionValidator
{
    protected $authModel;
    protected function authorize(int $roleId, int $operationId)
    {
        $this->authModel = new authModel();
        $hassPermission = $this->authModel->hasPermission($roleId, $operationId);

        if (!$hassPermission['result']) {
            http_response_code(200);
            echo json_encode(['result' => false, 'message' => 'Acceso denegado: permiso insuficiente']);
            exit;
        }
    }
}
