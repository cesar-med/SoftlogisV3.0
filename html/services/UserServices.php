<?php

include_once __DIR__ . "/../admin/users/model/UserModel.php";
include_once __DIR__ . "/../helpers/php/ValidatorHelper.php";
include_once __DIR__ . "/../helpers/php/LoggerHelper.php";

class UserServices
{
    private $model;
    private $validatorHelper;
    private $loggerHelper;
    private $fieldMap;
    private $filtersMap;
    public function __construct()
    {
        $this->model = new UserModel();
        $this->validatorHelper = new ValidatorHelper();
        $this->loggerHelper = new LoggerHelper();
        $this->fieldMap = [
            'search' => ['searchable' => true],
            'first_name' => ['field' => 'p.first_name', 'searchable' => true],
            'last_name' => ['field' => 'p.last_name', 'searchable' => true],
            'email' => ['field' => 'u.email', 'searchable' => true],
            'role_name' => ['field' => 'r.description', 'searchable' => true],
            'role_id' => ['field' => 'u.role_id'],
            'employee_id' => ['field' => 'u.employee_id'],
            'status' => ['field' => 'u.status'],
        ];
        $this->filtersMap = [
            'roles' => [
                'is_catalog' => true,
                'label' => 'Roles',
                'identifier' => "role_id",
                'catalog_name' => 'roles',
            ],
            'status' => [
                'is_catalog' => false,
                'label' => 'Estatus',
                'identifier' => "status",
                'data' => [
                    ['id' => '1', 'description' => 'Activos'],
                    ['id' => '0', 'description' => 'Inactivos']
                ],
            ],
        ];
    }

    public function listUsers($request, $filters = [])
    {

        $params = [];
        $filters = $this->validatorHelper->buildWhereClause($filters, $this->fieldMap, $params);

        $users['users'] = $this->model->getUsers($filters, $params, $request['page'], $request['countPerPage']);
        $count = $this->model->getCountUsers($filters);
        $users['registers'] = $this->model->getCountUsers($filters, $params)['data'][0]['total'] ?? 0;

        return $users;
    }
    public function listUsersExport($request, $filters = [])
    {

        $params = [];
        $filters = $this->validatorHelper->buildWhereClause($filters, $this->fieldMap, $params);

        $users = $this->model->getUsersExport($filters, $params);

        return $users;
    }
    public function getUserByUUID($request)
    {

        if (!$this->validatorHelper->validatorUUID($request['uuid'])) {
            return ['result' => false, 'message' => "El uuid no es valido,verificalo"];
        }

        $user = $this->model->sendQuery("authenticate.users", "employee_id,role_id,email,phone_number,status", " WHERE uuid=:uuid", [':uuid' => $request['uuid']]);

        return $user;
    }
    public function getFilters()
    {
        $filters = [];

        foreach ($this->filtersMap as $key => $value) {

            if ($value['is_catalog']) {
                $catalog = $this->getCatalogs(['type' => $value['catalog_name']]);

                if ($catalog[$value['catalog_name']]['result']) {
                    $filters[$key] = ['identifier' => $value['identifier'], 'label' => $value['label'], "data" => $catalog[$value['catalog_name']]['data']];
                } else {
                    $filters[$key] = ['identifier' => $value['identifier'], 'label' => $value['label'], 'data' => []];
                }
            } else {
                $filters[$key] = $value;
            }
        }
        return $filters;
    }
    public function getCatalogs($params)
    {
        if (!isset($params['type'])) {
            return ['error' => 'Tipo no definido'];
        }

        switch ($params['type']) {
            case 'all':
                $response['employees'] = $this->model->sendQuery('rh.employees', "id,CONCAT(first_name,' ',last_name) as description",  " WHERE status", []);
                $response['roles'] = $this->model->sendQuery('authenticate.roles', 'id,description', "WHERE status", []);

                return $response;
            case 'employees':
                return [
                    'employees' => $this->model->sendQuery('rh.employees', "id,CONCAT(first_name,' ',last_name) as description",  " WHERE status", [])
                ];
            case 'roles':
                return [
                    'roles' => $this->model->sendQuery('authenticate.roles', 'id,description', "WHERE status", [])
                ];
            default:
                return ['error' => 'Tipo de catálogo no reconocido'];
        }
    }
    public function create($user, $userId)
    {
        $rules = [
            'employee_id' => ['required' => true, 'unique' => 'users.employee_id'],
            'role_id' => ['required' => true, 'type' => 'int'],
            'email' => ['required' => true, 'type' => 'email', 'unique' => 'users.email'],
            'password' => [
                'required' => true,
                'password' => false, // validación de estructura fuerte
                'min' => 8,
                'max' => 64
            ],
            'password_confirm' => [
                'required' => true,
                'matches' => 'password'
                // debe coincidir con el campo password
            ]
        ];

        $errores = $this->validatorHelper->validateData($user, $rules);

        if (!empty($errores)) {
            return ['result' => false, 'message' => $errores];
        }

        unset($user['password_confirm']);

        $uuid = $this->validatorHelper->generateUUIDv4();

        $user['status'] ??= 0;

        $connection = $this->model->getConnection();

        $connection->beginTransaction();

        $user['uuid'] = $uuid;
        $createUser = $this->model->create($user);

        if (!$createUser['result']) {
            return $createUser;
        }

        $insertLogger = $this->loggerHelper->logAction($userId, 'Insert', 'Users', $user, "", "Nuevo usuario registrado con éxtio, uuid: $uuid");

        if (!$insertLogger['result']) {
            $connection->rollBack();
            error_log("Error al insertar el registro de auditoria modulo usuario accion insert id: {$createUser['id']}");
            return ['result' => false, 'message' => "Error al insertar el registro de auditoria modulo usuario accion insert id: {$createUser['id']}"];
        }

        $connection->commit();

        return ['result' => true, 'message' => "Usuario registrado con éxito"];
    }
    public function update($user, $userId)
    {

        $uuid = $user['uuid'];
        unset($user['uuid']);

        if (!$this->validatorHelper->validatorUUID($uuid)) {
            return ['result' => false, 'message' => "El UUID no es válido."];
        }

        $rules = [
            'employee_id' => ['required' => true, 'unique' => "users.employee_id,$uuid"],
            'role_id' => ['required' => true],
            'email' => ['required' => true, 'unique' => "users.email,$uuid"],
        ];

        if (isset($user['phone_number']) && !empty(trim($user['phone_number']))) {
            $rules['phone_number'] = ['unique' => "users.phone_number,$uuid"];
        }

        if (isset($user['password']) && !empty(trim($user['password']))) {
            $rules['password'] = [
                'required' => true,
                'password' => false, // validación de estructura fuerte
                'min' => 8,
                'max' => 64
            ];
            $rules['password_confirm'] = [
                'required' => true,
                'matches' => 'password'
                // debe coincidir con el campo password
            ];
            unset($user['password_confirm']);
        } else {
            unset($user['password_confirm'], $user['password']);
        }

        $errores = $this->validatorHelper->validateData($user, $rules);

        if (!empty($errores)) {
            return ['result' => false, 'message' => $errores];
        }

        $connection = $this->model->getConnection();

        $connection->beginTransaction();

        $user['status'] ??= 0;

        $update = $this->model->update($user, $uuid);

        if (!$update['result']) {
            $connection->rollBack();
            return $update;
        }

        $insertLogger = $this->loggerHelper->logAction($userId, 'Update', 'Users', $user, "", "Usuario actualizado con éxtio, uuid: $uuid");

        if (!$insertLogger['result']) {
            $connection->rollBack();
            error_log("Error al insertar el registro de auditoria modulo usuario accion update uuid: $uuid");
            return ['result' => false, 'message' => "Error al insertar el registro de auditoria modulo usuario accion update uuid: $uuid"];
        }

        $connection->commit();

        return ['result' => true, 'message' => "Usuario actualizado con éxito"];
    }
    // public function getFiltersCatalogs($filtersMap)
    // {
    //     $filters = [];

    //     foreach ($filtersMap as $key => $filter) {

    //         $filters[$key] = [
    //             'identifier' => $filter['identifier'],
    //             'label' => $filter['label'],
    //             'data' => $filter['catalog'] ? call_user_func($this->model, $filter['table'])['data'] : $filter['data'],
    //         ];
    //     }

    //     return $filters;
    // }
}
