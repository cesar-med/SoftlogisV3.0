<?php


require_once __DIR__ . '/../../auth/controller/authController.php';
require_once __DIR__ . '/../../admin/users/controller/UserController.php';
require_once __DIR__ . '/../../rh/employees/controller/EmployeeController.php';
require_once __DIR__ . '/../../api/controller/ApiController.php';
// require_once __DIR__ . '/../../admin/roles/controller/roleController.php';
require_once __DIR__ . '/routesSystem.php';

// Capturar la ruta y método
$route = $_GET['route'] ?? null;
$method = $_SERVER['REQUEST_METHOD'];
$userSession = authController::getUserSession();
// Capturar datos de la solicitud (POST o JSON)

$downloadEndpoints = [
    'exports/docs', // ← rutas que devuelven archivos
    'images/catalogs',
];

if (!in_array($route, $downloadEndpoints)) {
    header("Content-Type: application/json");
}

switch ($method) {
    case 'POST':
        if (isset($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
            $inputData = json_decode(file_get_contents("php://input"), true) ?? [];
        } else {
            $inputData = $_POST;
            // Agrega archivos si vienen
            if (!empty($_FILES)) {
                $inputData['files'] = $_FILES;
            }
        }
        break;
    case 'GET':
        $inputData = $_GET;
        break;
    default:
        $inputData = [];
}

try {

    // Verificar si la ruta y el método existen
    if (!isset(routesSystem[$method][$route])) {
        throw new Exception("Ruta no encontrada", 404);
    }

    // Obtener el controlador y método
    [$controller, $action] = routesSystem[$method][$route];

    // Verificar si el controlador existe
    if (!class_exists($controller)) {
        throw new Exception("El controlador '$controller' no existe", 500);
    }

    // Instanciar el controlador
    $controllerInstance = new $controller();

    // Verificar si el método existe
    if (!method_exists($controllerInstance, $action)) {
        throw new Exception("Método '$action' no encontrado en '$controller'", 500);
    }

    $inputData['userSession'] = $userSession;
    // Llamar al método del controlador
    $response = call_user_func([$controllerInstance, $action], $inputData);

    echo json_encode($response);
} catch (Exception $e) {
    print_r($e->getMessage());
    $code = (int) $e->getCode();
    http_response_code(($code >= 100 && $code <= 599) ? $code : 400);
    echo json_encode(["result" => false, "error" => $e->getMessage()]);
}
