<?php

include_once __DIR__ . "/../../../connection/core.php";

class EmployeeModel
{

    private $coreDB;
    public function __construct()
    {
        $this->coreDB = new Database("rh");
    }
    public function getConnection()
    {
        return $this->coreDB->getInstance();
    }
    public function getEmployees(string $filter = "", $params = [], $page = 1, $countPerPage = 50)
    {

        $offset = ($page - 1) * $countPerPage;
        $limit = "LIMIT $countPerPage OFFSET $offset";

        $query =
            " SELECT 
                p.uuid,
                CONCAT(p.last_name,' ',p.first_name) as name,
                pos.name as position,
                dep.name as department,
                p.email,
                p.phone_number,
                DATE_FORMAT(p.date_of_joining,'%d-%m-%Y') as date_joining,
                p.status
                FROM rh.employees p
                LEFT JOIN subcatalogs.positions pos ON p.position_id=pos.id
                LEFT JOIN subcatalogs.departments dep ON p.department_id=dep.id
                $filter
                ORDER BY p.last_name ASC
                $limit
            ";

        $dataEmployee = $this->coreDB->readDb($query, $params);

        return $dataEmployee;
    }
    public function getEmployeesExport(string $filter = "", $params = [])
    {

        $query = "SELECT 
				CONCAT(p.first_name,' ',p.last_name) as nombre,
				u.email,
				u.phone_number as telefono,
				r.description as rol,
				IF(u.status,'Activo','Baja') as estatus
				FROM rh.employees u
				INNER JOIN authenticate.roles r ON u.role_id = r.id
				INNER JOIN rh.employees p ON u.employee_id = p.id
                $filter
                ORDER BY p.first_name";

        $dataEmployee = $this->coreDB->readDb($query, $params);

        return $dataEmployee;
    }
    public function getCountEmployees(string $filter = "", $params = [])
    {

        $query = "SELECT 
				COUNT(p.id) as total
				FROM rh.employees p
                LEFT JOIN subcatalogs.positions pos ON p.position_id=pos.id
                LEFT JOIN subcatalogs.departments dep ON p.department_id=dep.id
                $filter
                ORDER BY p.last_name ASC";

        $countEmployee = $this->coreDB->readDb($query, $params);

        return $countEmployee;
    }
    public function sendQuery($table, $columns, $filters = "", $params = [], $orderBy = null)
    {

        $query = "SELECT $columns FROM $table $filters";
        if ($orderBy) {
            $query .= " ORDER BY $orderBy";
        }

        $dataEmployee = $this->coreDB->readDb($query, $params);

        return $dataEmployee;
    }
    public function create($employee)
    {

        $createEmployee = $this->coreDB->insertDB("rh.employees", $employee);

        return $createEmployee;
    }
    public function update($employee, $uuid)
    {

        $createEmployee = $this->coreDB->updateDB("rh.employees", $employee, $uuid);

        return $createEmployee;
    }
}
