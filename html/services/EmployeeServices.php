<?php

include_once __DIR__ . "/../rh/employees/model/EmployeeModel.php";
include_once __DIR__ . "/../helpers/php/ValidatorHelper.php";
include_once __DIR__ . "/../helpers/php/LoggerHelper.php";

class EmployeeServices
{
    private $model;
    private $validatorHelper;
    private $loggerHelper;
    private $fieldMap;
    private $filtersMap;
    public function __construct()
    {
        $this->model = new EmployeeModel();
        $this->validatorHelper = new ValidatorHelper();
        $this->loggerHelper = new LoggerHelper();
        $this->fieldMap = [
            'search' => ['searchable' => true],
            'first_name' => ['field' => 'p.first_name', 'searchable' => true],
            'last_name' => ['field' => 'p.last_name', 'searchable' => true],
            'email' => ['field' => 'p.email', 'searchable' => true],
            'tax_id' => ['field' => 'p.tax_id', 'searchable' => true],
            'personal_id' => ['field' => 'p.personal_id', 'searchable' => true],
            'work_shift_id' => ['field' => 'p.work_shift_id'],
            'employee_type_id' => ['field' => 'p.employee_type_id'],
            'office_id' => ['field' => 'p.office_id'],
            'department_id' => ['field' => 'p.department_id'],
            'position_id' => ['field' => 'p.position_id'],
            'contract_type_id' => ['field' => 'p.contract_type_id'],
            'status' => ['field' => 'p.status'],
        ];
        $this->filtersMap = [
            'employee_types' => [
                'is_catalog' => true,
                'label' => 'Tipo de empleado',
                'identifier' => "employee_type_id",
                'catalog_name' => 'employee_types',
            ],
            'work_shifts' => [
                'is_catalog' => true,
                'label' => 'Turnos',
                'identifier' => "work_shift_id",
                'catalog_name' => 'work_shifts',
            ],
            'offices' => [
                'is_catalog' => true,
                'label' => 'Oficinas',
                'identifier' => "office_id",
                'catalog_name' => 'offices',
            ],
            'departments' => [
                'is_catalog' => true,
                'label' => 'Departamentos',
                'identifier' => "department_id",
                'catalog_name' => 'departments',
            ],
            'positions' => [
                'is_catalog' => true,
                'label' => 'Puestos',
                'identifier' => "position_id",
                'catalog_name' => 'positions',
            ],
            'contracts' => [
                'is_catalog' => true,
                'label' => 'Contratos',
                'identifier' => "contract_id",
                'catalog_name' => 'contract_types',
            ],
            'status' => [
                'is_catalog' => false,
                'label' => 'Estatus',
                'identifier' => "status",
                'data' => [
                    ['id' => '1', 'description' => 'Activos'],
                    ['id' => '0', 'description' => 'Bajas']
                ],
            ],
        ];
    }

    public function listEmployees($request, $filters = [])
    {

        $params = [];
        $filters = $this->validatorHelper->buildWhereClause($filters, $this->fieldMap, $params);

        $employees['employees'] = $this->model->getEmployees($filters, $params, $request['page'], $request['countPerPage']);
        $employees['registers'] = $this->model->getCountEmployees($filters, $params)['data'][0]['total'] ?? 0;

        return $employees;
    }
    public function listEmployeesExport($request, $filters = [])
    {

        $params = [];
        $filters = $this->validatorHelper->buildWhereClause($filters, $this->fieldMap, $params);

        $employees = $this->model->getEmployeesExport($filters, $params);

        return $employees;
    }
    public function getEmployeeByUUID($request)
    {

        if (!$this->validatorHelper->validatorUUID($request['uuid'])) {
            return ['result' => false, 'message' => "El uuid no es valido,verificalo"];
        }

        $employee = $this->model->sendQuery("rh.employees", "*", " WHERE uuid=:uuid", [':uuid' => $request['uuid']]);

        return $employee;
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
                $response['employee_types'] = $this->model->sendQuery('subcatalogs.employee_types', "id,name as description",  " WHERE status", []);
                $response['work_shifts'] = $this->model->sendQuery('subcatalogs.work_shifts', "id,name as description",  " WHERE status", []);
                $response['offices'] = $this->model->sendQuery('subcatalogs.offices', "id,name as description",  " WHERE status", []);
                $response['departments'] = $this->model->sendQuery('subcatalogs.departments', "id,name as description",  " WHERE status", []);
                $response['positions'] = $this->model->sendQuery('subcatalogs.positions', "id,name as description",  " WHERE status", []);
                $response['contract_types'] = $this->model->sendQuery('subcatalogs.contract_types', "id,name as description",  " WHERE status", []);

                return $response;
            case 'employee_types':
                return [
                    'employee_types' => $this->model->sendQuery('subcatalogs.employee_types', "id,name as description",  " WHERE status", [])
                ];
            case 'work_shifts':
                return [
                    'work_shifts' => $this->model->sendQuery('subcatalogs.work_shifts', "id,name as description",  " WHERE status", [])
                ];
            case 'offices':
                return [
                    'offices' => $this->model->sendQuery('subcatalogs.offices', "id,name as description",  " WHERE status", [])
                ];
            case 'departments':
                return [
                    'departments' => $this->model->sendQuery('subcatalogs.departments', "id,name as description",  " WHERE status", [])
                ];
            case 'positions':
                return [
                    'positions' => $this->model->sendQuery('subcatalogs.positions', "id,name as description",  " WHERE status", [])
                ];
            case 'contract_types':
                return [
                    'contract_types' => $this->model->sendQuery('subcatalogs.contract_types', "id,name as description",  " WHERE status", [])
                ];
            default:
                return ['error' => 'Tipo de catálogo no reconocido'];
        }
    }
    public function create($employee, $employeeId)
    {
        $rules = [
            'employee_id' => ['required' => true, 'unique' => 'employees.employee_id'],
            'role_id' => ['required' => true, 'type' => 'int'],
            'email' => ['required' => true, 'type' => 'email', 'unique' => 'employees.email'],
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

        $errores = $this->validatorHelper->validateData($employee, $rules);

        if (!empty($errores)) {
            return ['result' => false, 'message' => $errores];
        }

        unset($employee['password_confirm']);

        $uuid = $this->validatorHelper->generateUUIDv4();

        $employee['status'] ??= 0;

        $connection = $this->model->getConnection();

        $connection->beginTransaction();

        $employee['uuid'] = $uuid;
        $createEmployee = $this->model->create($employee);

        if (!$createEmployee['result']) {
            return $createEmployee;
        }

        $insertLogger = $this->loggerHelper->logAction($employeeId, 'Insert', 'Employees', $employee, "", "Nuevo usuario registrado con éxtio, uuid: $uuid");

        if (!$insertLogger['result']) {
            $connection->rollBack();
            error_log("Error al insertar el registro de auditoria modulo usuario accion insert id: {$createEmployee['id']}");
            return ['result' => false, 'message' => "Error al insertar el registro de auditoria modulo usuario accion insert id: {$createEmployee['id']}"];
        }

        $connection->commit();

        return ['result' => true, 'message' => "Usuario registrado con éxito"];
    }
    public function update($employee, $employeeId)
    {

        $uuid = $employee['uuid'];
        unset($employee['uuid']);

        if (!$this->validatorHelper->validatorUUID($uuid)) {
            return ['result' => false, 'message' => "El UUID no es válido."];
        }

        $rules = [
            'employee_id' => ['required' => true, 'unique' => "employees.employee_id,$uuid"],
            'role_id' => ['required' => true],
            'email' => ['required' => true, 'unique' => "employees.email,$uuid"],
        ];

        if (isset($employee['phone_number']) && !empty(trim($employee['phone_number']))) {
            $rules['phone_number'] = ['unique' => "employees.phone_number,$uuid"];
        }

        if (isset($employee['password']) && !empty(trim($employee['password']))) {
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
            unset($employee['password_confirm']);
        } else {
            unset($employee['password_confirm'], $employee['password']);
        }

        $errores = $this->validatorHelper->validateData($employee, $rules);

        if (!empty($errores)) {
            return ['result' => false, 'message' => $errores];
        }

        $connection = $this->model->getConnection();

        $connection->beginTransaction();

        $employee['status'] ??= 0;

        $update = $this->model->update($employee, $uuid);

        if (!$update['result']) {
            $connection->rollBack();
            return $update;
        }

        $insertLogger = $this->loggerHelper->logAction($employeeId, 'Update', 'Employees', $employee, "", "Usuario actualizado con éxtio, uuid: $uuid");

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
    //             'data' => $filter['catalog'] ? call_employee_func($this->model, $filter['table'])['data'] : $filter['data'],
    //         ];
    //     }

    //     return $filters;
    // }
}
