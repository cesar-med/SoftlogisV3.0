<?php
include_once __DIR__ . "/../../../services/EmployeeServices.php";
include_once __DIR__ . "/../../../auth/controller/PermissionValidator.php";

class EmployeeController extends PermissionValidator
{
    private $services;

    public function __construct()
    {
        $this->services = new EmployeeServices();
    }
    public function create($employee)
    {
        try {
            if (empty($employee['userSession']['roleSession'])) {
                return ['result' => false, 'message' => 'No se encontró información de sesión válida.'];
            }

            $this->authorize($employee['userSession']['roleSession'], 324);

            $employeeId = $employee['userSession']['userSession'] ?? null;
            unset($employee['userSession']);

            if (!$employeeId) {
                error_log("Error al obtener el id de la sesión");
                return ['result' => false, 'message' => 'No se pudo identificar el empleado en sesión.'];
            }

            return $this->services->create($employee, $employeeId);
        } catch (Exception $e) {
            error_log("Error en controlador insert: " . $e->getMessage());
            return ['result' => false, 'message' => 'Error interno al crear el empleado.'];
        }
    }

    public function update($employee)
    {
        try {

            if (empty($employee['userSession']['roleSession'])) {
                return ['result' => false, 'message' => 'No se encontró información de sesión válida.'];
            }

            $this->authorize($employee['userSession']['roleSession'], 201);

            $employeeId = $employee['userSession']['userSession'] ?? null;
            unset($employee['userSession']);

            if (!$employeeId) {
                error_log("Error al obtener el id de la sesión");
                return ['result' => false, 'message' => 'No se pudo identificar el empleado en sesión.'];
            }

            return $this->services->update($employee, $employeeId);
        } catch (Exception $e) {
            error_log("Error en controlador update: " . $e->getMessage());
            return ['result' => false, 'message' => 'Error interno al actualizar el empleado.'];
        }
    }
    public function getInitialData($request)
    {

        $data = $this->services->listEmployees($request);
        $data['filters'] = $this->services->getFilters();

        return $data;
    }
    public function getEmployees($request)
    {

        $this->authorize($request['userSession']['roleSession'], 200);

        $filters = isset($request['filters']) && (!empty(trim($request['filters']))) ? json_decode($request['filters'], true) : [];

        $listEmployees = $this->services->listEmployees($request, $filters);

        return $listEmployees;
    }
    public function employeesListExport($request)
    {

        $this->authorize($request['userSession']['roleSession'], 203);

        $filters = isset($request['filters']) && (!empty(trim($request['filters']))) ? json_decode($request['filters'], true) : [];

        $listEmployees = $this->services->listEmployeesExport($request, $filters);

        return $listEmployees;
    }
    public function getEmployeeByUUID($request)
    {

        $this->authorize($request['userSession']['roleSession'], 201);

        $employee = $this->services->getEmployeeByUUID($request);

        return $employee;
    }
    public function getCatalogs($params): array
    {
        return $this->services->getCatalogs($params);
    }
}
