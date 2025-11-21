<?php
require_once __DIR__ . "/../../connection/core.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class authModel
{
    private $coreDB;

    public function __construct()
    {
        $this->coreDB = new Database("authenticate");
    }

    public function getUser(string $username): array
    {


        $query = "SELECT  
            us.role_id,
            us.id,
            us.email,
            us.phone_number,
            us.password,
            CONCAT(first_name,' ',last_name,' ') as name
        FROM authenticate.users us
        LEFT JOIN rh.employees emp ON us.employee_id=emp.id
        WHERE us.email = :username or us.phone_number=:phone_number";

        $userData = $this->coreDB->readDb($query, [':username' => $username, ':phone_number' => $username]);

        if (!$userData['result']) {
            return ['result' => false, 'message' => "Error al obtener la información del usuario:{$userData['message']}"];
        }

        return ['result' => true, 'data' => $userData['data'][0]];
    }
    public function getUserById(int $userId): array
    {
        try {

            $query = "SELECT user.id,CONCAT(emp.first_name,' ',emp.last_name) as name,user.email as username,user.role_id,role.description as role
            FROM authenticate.users user 
            LEFT JOIN rh.employees emp ON user.employee_id=emp.id
            LEFT JOIN authenticate.roles role ON user.role_id=role.id
            WHERE user.id = :userId";

            $user = $this->coreDB->readDb($query, [':userId' => $userId]);

            return $user;
        } catch (PDOException $e) {
            return ['result' => false, 'message' => $e->getMessage()];
        }
    }
    public function getPermissionsUser(int $roleId): array
    {
        try {

            $query = "SELECT 
                        	c.id,
                            c.description AS funcion,
                        	c.class,
                            c.path,
                            c.module_group AS module
                        FROM authenticate.role_profile a 
                        JOIN authenticate.operations b ON a.operation_id = b.id
                        JOIN authenticate.modules c ON b.module_id = c.id
                        WHERE a.role_id = :role_id and c.status
                        GROUP BY c.description,c.module_group,c.id
                        ORDER BY c.module_group ASC";

            $data = $this->coreDB->readDb($query, [':role_id' => $roleId]);

            if ($data) {
                return ['result' => true, 'permissions' => $this->orderPermissions($data)];
            } else {
                return ['result' => false, 'message' => 'No hay permisos'];
            }
        } catch (PDOException $e) {
            return ['result' => false, 'message' => $e->getMessage()];
        }
    }
    public function hasPermission(int $roleId, int $operationId)
    {

        $data = $this->coreDB->readDb("SELECT 
                                            id,
                                            status
                                            FROM authenticate.role_profile
                                            WHERE role_id=:roleId and operation_id=:operationId and status
                                            ", ['roleId' => $roleId, 'operationId' => $operationId]);

        return $data;
    }
    public function createSession(array $session): array
    {

        $user_id = $session['payload']['user_id'];
        $name = $session['payload']['name'];
        $role_id = $session['payload']['role_id'];
        $iat     = $session['payload']['iat'];
        $exp     = $session['payload']['exp'];

        $iat = date('Y-m-d H:i', $iat);
        $exp = date('Y-m-d H:i', $exp);

        // Validación de parámetros
        if (!is_int($user_id) || empty($session['token'])) {
            return ['result' => false, 'message' => 'Parámetros de entrada inválidos'];
        }

        // Iniciar la transacción
        $this->coreDB->pdo->beginTransaction();

        // Insertar la nueva sesión

        $data = [
            'user_id' => $user_id,
            'token' => $session['token'],
            'created_at' => $iat,
            'validated_at' => $exp
        ];

        $sessionInsert = $this->coreDB->insertDB("authenticate.sessions", $data);

        if (!$sessionInsert['result']) {
            $this->coreDB->pdo->rollBack();
            return ['result' => false, 'message' => "Error al guardar la sesión en la base de datos: {$sessionInsert['message']}"];
        }

        // Confirmar la transacción
        $this->coreDB->pdo->commit();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['user'] = [
            'user_id' => $user_id,
            'name' => $name,
            'role_id' => $role_id,
            'jwt' => $session['token'],
        ];

        return ['result' => true, 'token' => $session['token']];
    }
    public function verifySession(int $user_id, string $token): bool
    {

        try {

            $session = $this->coreDB->readDb("SELECT * FROM authenticate.sessions WHERE token = :token AND validate_at > NOW()", [':token' => $token]);

            return $session ? true : false;
        } catch (PDOException $e) {

            error_log($e->getMessage());
            return false;
        }
    }
    public function generateJWTSession(array $user): array
    {
        try {
            // Cargar variables de entorno

            if (!isset($_ENV['JWT_SECRET']) || empty($_ENV['JWT_SECRET'])) {
                throw new Exception("La clave secreta JWT no está configurada.");
            }

            $key = $_ENV['JWT_SECRET'];

            // Validar que los datos del usuario sean correctos
            if (!isset($user['id']) || !isset($user['role_id'])) {
                throw new Exception("Datos de usuario incompletos.");
            }

            $payload = [
                "user_id" => $user['id'],
                "name" => $user['name'],
                "role_id" => $user['role_id'],
                "iat" => time(),            // Tiempo de emisión
                "exp" => time() + 3600      // Expira en 1 dia
            ];
            $header = [
                "alg" => "HS256",
                "typ" => "JWT",
                "kid" => "clave_secreta_1" // Puedes cambiar este valor según tu necesidad
            ];

            // Intentar generar el token
            $jwt = JWT::encode($payload, $key, 'HS256', null, $header);

            return ['result' => true, 'token' => $jwt, 'payload' =>  $payload];
        } catch (Exception $e) {
            return ['result' => false, 'message' => $e->getMessage()];
        }
    }
    public function validateJwt($token): array
    {
        try {

            $secret = $_ENV['JWT_SECRET'];
            $decoded = JWT::decode($token, new Key($secret, 'HS256'));

            return ['result' => true, "token" => $decoded];
        } catch (Exception $e) {
            return ['result' => false, "message" => "Error al decodificar el token", "e" => $e->getMessage()];  // Si el token es inválido, retorna null
        }
    }
    public function destroySession(): bool
    {
        session_start();

        $token = $_SESSION['user']['jwt'] ?? null;

        if (!$token) {
            return false;
        }

        // Marcar sesión como finalizada en la base de datos
        $send = $this->coreDB->readDb("
        UPDATE authenticate.sessions 
        SET status = 0, logout_at = NOW() 
        WHERE token = :token", [':token' => $token]);

        if (!$send) {
            return false;
        }

        // // Limpiar todas las variables de sesión
        $_SESSION = [];

        // Eliminar la cookie de sesión si existe
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        // Destruir la sesión
        session_destroy();

        return true;
    }
    public static function getUserSession(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $user = $_SESSION['user'] ?? null;

        if ($user) {
            return ['result' => true, 'user' => $user];
        } else {
            return ['result' => false];
        }
    }
    private function orderPermissions($data): array
    {

        if ($data['result']) {

            unset($data['result']);

            $data = $data['data'];
            $nuevoArray = [];

            foreach ($data as $item) {
                $modulo = $item['module'];
                $id = $item['id'];
                $funcion = $item['funcion'];
                $class = $item['class'];
                $path = $item['path'];
                if (!isset($nuevoArray[$modulo])) {
                    $nuevoArray[$modulo] = [];
                }
                $nuevoArray[$modulo][] = ['id' => $id, 'funcion' => $funcion, 'class' => $class, 'path' => $path];
            }
        }
        return $nuevoArray;
    }
}
