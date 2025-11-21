<?php

require_once __DIR__ . "/../model/authModel.php";
require_once __DIR__ . "/../../vendor/autoload.php";

use Dotenv\Dotenv;

class authController
{

    private $model;
    public $pathLogin;
    public function __construct()
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . "/../../");
        $dotenv->load();
        $this->pathLogin = $_ENV['LOGIN'];
        $this->model = new authModel();
    }
    public function auth($data): array
    {
        $dataToken = $this->validateJWT($data['token']);
        return $dataToken;
    }
    public function login(array $credentials): array
    {

        try {

            $username = $credentials['username'];
            $password = $credentials['password'];

            $user = $this->model->getUser($username);

            if (!$user['result']) {
                throw new Exception($user['message']);
            }

            $user = $user['data'];

            if (!password_verify($password, $user['password'])) {
                throw new Exception('El password no es valido');
            }

            unset($user['password']);

            $session =  $this->model->generateJWTSession($user);

            if (!$session['result']) {
                throw new Exception($session['message']);
            }

            return $this->model->createSession($session);
        } catch (Exception $e) {

            error_log($e->getMessage());
            return ['result' => false, 'message' => $e->getMessage()];
        }
    }
    public function logout()
    {

        $logout =  $this->model->destroySession();

        if ($logout) {
            header("Location: $this->pathLogin"); // o la ruta que uses para login
            exit;
        }
    }
    public function getModulesUser()
    {

        $userLogin = $this->model->getUserSession();

        if (!$userLogin['result']) {

            return $userLogin;
        }

        $userInfo = $this->model->getUserById($userLogin['user']['user_id']);

        if (!$userInfo['result']) {

            error_log($userInfo['message']);
            return ['result' => false];
        }

        $userInfo = $userInfo['data'][0];

        $userPermissions = $this->model->getPermissionsUser($userInfo['role_id']);

        if (!$userPermissions['result']) {
            error_log($userPermissions['message']);
            return ['result' => false];
        }

        $userPermissions['info'] = $userInfo;

        return $userPermissions;
    }
    public function validateJWT($token)
    {

        return $this->model->validateJwt($token);
    }
    public function isSessionActive($user_id, $token)
    {

        return $this->model->verifySession($user_id, $token);
    }
    public function generateJWTSession($user)
    {
        return $this->model->generateJWTSession($user);
    }
    public static function getUserSession()
    {
        $userLogin = authModel::getUserSession();

        if (!$userLogin['result']) {

            return $userLogin;
        }

        return ['userSession' => $userLogin['user']['user_id'], 'roleSession' => $userLogin['user']['role_id']];
    }
}
