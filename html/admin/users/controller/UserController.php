<?php
include_once __DIR__ . "/../../../services/UserServices.php";
include_once __DIR__ . "/../../../auth/controller/PermissionValidator.php";

class UserController extends PermissionValidator
{
    private $services;
    private $validatorHelper;

    public function __construct()
    {
        $this->services = new UserServices();
        $this->validatorHelper = new ValidatorHelper();
    }
    public function create($user)
    {
        try {
            if (empty($user['userSession']['roleSession'])) {
                return ['result' => false, 'message' => 'No se encontró información de sesión válida.'];
            }

            $this->authorize($user['userSession']['roleSession'], 324);

            $userId = $user['userSession']['userSession'] ?? null;
            unset($user['userSession']);

            if (!$userId) {
                error_log("Error al obtener el id de la sesión");
                return ['result' => false, 'message' => 'No se pudo identificar el usuario en sesión.'];
            }

            return $this->services->create($user, $userId);
        } catch (Exception $e) {
            error_log("Error en controlador insert: " . $e->getMessage());
            return ['result' => false, 'message' => 'Error interno al crear el usuario.'];
        }
    }

    public function update($user)
    {
        try {

            if (empty($user['userSession']['roleSession'])) {
                return ['result' => false, 'message' => 'No se encontró información de sesión válida.'];
            }

            $this->authorize($user['userSession']['roleSession'], 201);

            $userId = $user['userSession']['userSession'] ?? null;
            unset($user['userSession']);

            if (!$userId) {
                error_log("Error al obtener el id de la sesión");
                return ['result' => false, 'message' => 'No se pudo identificar el usuario en sesión.'];
            }

            return $this->services->update($user, $userId);
        } catch (Exception $e) {
            error_log("Error en controlador update: " . $e->getMessage());
            return ['result' => false, 'message' => 'Error interno al actualizar el usuario.'];
        }
    }
    public function getInitialData($request)
    {

        $data = $this->services->listUsers($request);
        $data['filters'] = $this->services->getFilters();

        return $data;
    }
    public function getUsers($request)
    {

        $this->authorize($request['userSession']['roleSession'], 200);

        $filters = isset($request['filters']) && (!empty(trim($request['filters']))) ? json_decode($request['filters'], true) : [];

        $listUsers = $this->services->listUsers($request, $filters);

        return $listUsers;
    }
    public function usersListExport($request)
    {

        $this->authorize($request['userSession']['roleSession'], 203);

        $filters = isset($request['filters']) && (!empty(trim($request['filters']))) ? json_decode($request['filters'], true) : [];

        $listUsers = $this->services->listUsersExport($request, $filters);

        return $listUsers;
    }
    public function getUserByUUID($request)
    {

        $this->authorize($request['userSession']['roleSession'], 201);

        $user = $this->services->getUserByUUID($request);

        return $user;
    }
    public function getCatalogs($params): array
    {
        return $this->services->getCatalogs($params);
    }
}
