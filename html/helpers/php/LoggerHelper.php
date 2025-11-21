<?php
include_once __DIR__ . "/../../connection/core.php";
class LoggerHelper
{
    private $coreDB;
    public function __construct()
    {
        $this->coreDB = new Database("authenticate");
    }

    public function logAction($userId, $action, $module, $data = [], $reference = null, $message = null)
    {

        // Validación básica
        if (empty($action) || empty($module)) {
            return ['result' => false, 'message' => 'Accion o modulo no definidos'];
        }

        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

        $data = [
            'user_id'   => $userId,
            'action'    => $action,
            'module'    => $module,
            'data_json' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'reference' => $reference,
            'message'   => $message,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent
        ];

        $inserLogger = $this->coreDB->insertDB("audits.loggers", $data);

        return $inserLogger;
    }
}
