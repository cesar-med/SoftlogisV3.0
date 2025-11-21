<?php

include_once __DIR__ . "/../../../connection/core.php";

class UserModel
{

    private $coreDB;
    public function __construct()
    {
        $this->coreDB = new Database("authenticate");
    }
    public function getConnection()
    {
        return $this->coreDB->getInstance();
    }
    public function getUsers(string $filter = "", $params = [], $page = 1, $countPerPage = 50)
    {

        $offset = ($page - 1) * $countPerPage;
        $limit = "LIMIT $countPerPage OFFSET $offset";

        $query = "SELECT 
				u.uuid,
				CONCAT(p.first_name,' ',p.last_name) as nombre,
				u.email,
				u.phone_number,
				r.description as rol,
				u.status
				FROM authenticate.users u
				INNER JOIN authenticate.roles r ON u.role_id = r.id
				INNER JOIN rh.employees p ON u.employee_id = p.id
                $filter
                ORDER BY p.first_name
				$limit";

        $dataUser = $this->coreDB->readDb($query, $params);

        return $dataUser;
    }
    public function getUsersExport(string $filter = "", $params = [])
    {

        $query = "SELECT 
				CONCAT(p.first_name,' ',p.last_name) as nombre,
				u.email,
				u.phone_number as telefono,
				r.description as rol,
				IF(u.status,'Activo','Baja') as estatus
				FROM authenticate.users u
				INNER JOIN authenticate.roles r ON u.role_id = r.id
				INNER JOIN rh.employees p ON u.employee_id = p.id
                $filter
                ORDER BY p.first_name";

        $dataUser = $this->coreDB->readDb($query, $params);

        return $dataUser;
    }
    public function getCountUsers(string $filter = "", $params = [])
    {

        $query = "SELECT 
				COUNT(u.id) as total
				FROM authenticate.users u
				INNER JOIN authenticate.roles r ON u.role_id = r.id
				INNER JOIN rh.employees p ON u.employee_id = p.id
                $filter";

        $countUser = $this->coreDB->readDb($query, $params);

        return $countUser;
    }
    public function getUserInfo(string $username)
    {

        $query = "SELECT 
                    *
                FROM users
                WHERE email=:username or phone_number=:username
        ";
        $dataUser = $this->coreDB->readDb($query, [':username' => $username]);

        return $dataUser;
    }
    public function sendQuery($table, $columns, $filters = "", $params = [], $orderBy = null)
    {

        $query = "SELECT $columns FROM $table $filters";
        if ($orderBy) {
            $query .= " ORDER BY $orderBy";
        }

        $dataUser = $this->coreDB->readDb($query, $params);

        return $dataUser;
    }
    public function create($user)
    {

        $createUser = $this->coreDB->insertDB("authenticate.users", $user);

        return $createUser;
    }
    public function update($user, $uuid)
    {

        $createUser = $this->coreDB->updateDB("authenticate.users", $user, $uuid);

        return $createUser;
    }
}
