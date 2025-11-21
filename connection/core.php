<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

class Database
{
    /** @var PDO[] Conexiones por nombre de base */
    private static array $instances = [];
    public $pdo;

    /** Evitar instanciación directa */
    public function __construct(string $db)
    {
        $this->pdo = $this->getInstance($db);
    }

    /** @var bool Carga de .env solo una vez */
    private static bool $envLoaded = false;

    /**
     * Obtener instancia de PDO
     *
     * @param string|null $dbName Nombre de la base de datos (opcional)
     * @return PDO
     * @throws PDOException
     */
    public function getInstance(?string $dbName = null): PDO
    {
        // Cargar variables de entorno una sola vez
        if (!self::$envLoaded) {
            $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
            $dotenv->safeLoad(); // safeLoad evita error si .env no existe
            self::$envLoaded = true;
        }

        // Definir valores por defecto si no se pasan
        $dbName = $dbName ?? ($_ENV['DB_NAME'] ?? '');
        $host   = $_ENV['DB_HOST'] ?? '127.0.0.1';
        $port   = $_ENV['DB_PORT'] ?? '3306';
        $user   = $_ENV['DB_USER'] ?? 'root';
        $pass   = $_ENV['DB_PASS'] ?? '';
        $charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

        if (!$dbName) {
            throw new RuntimeException("No se especificó el nombre de la base de datos y no existe valor por defecto.");
        }

        // Reusar conexión si ya existe
        if (isset(self::$instances[$dbName])) {
            return self::$instances[$dbName];
        }

        try {
            $dsn = sprintf("mysql:host=%s;port=%s;dbname=%s;charset=%s", $host, $port, $dbName, $charset);

            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);

            self::$instances[$dbName] = $pdo;

            return $pdo;
        } catch (PDOException $e) {
            error_log("Error al conectar con la base '$dbName': " . $e->getMessage());
            throw $e;
        }
    }
    public function readDb(string $query, array $params)
    {
        try {
            $stmt = $this->pdo->prepare($query);

            $stmt->execute($params);

            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return ['result' => true, 'data' => $data];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return ['result' => false, 'message' => $e->getMessage()];
        }
    }
    public function insertDB(string $db, array $data)
    {
        try {

            // Obtener nombres de campos y sus placeholders
            $fields = array_keys($data);
            $columns = implode(', ', $fields);
            $placeholders = ':' . implode(', :', $fields);

            // Preparar y ejecutar la sentencia
            $sql = "INSERT INTO $db ($columns) VALUES ($placeholders)";
            $stmt = $this->pdo->prepare($sql);

            $send = $stmt->execute($data);

            if (!$send) {
                throw new PDOException("No se pudo insertar la orden.");
            }

            $lastId = $this->pdo->lastInsertId();
            return ['result' => true, 'id' => $lastId];
        } catch (PDOException $e) {
            echo $e->getMessage();
            error_log("Error al insertar el usuario: " . $e->getMessage());
            return ['result' => false, 'message' => $e->getMessage()];
        }
    }
    public function updateDB(string $table, array $data, $uuid = null)
    {
        try {
            if (!$uuid) {
                throw new PDOException("Error en el UUID, verifíquelo.");
            }

            // Construir la parte SET dinámicamente: campo1 = :campo1, campo2 = :campo2
            $fields = array_keys($data);
            $setClause = implode(', ', array_map(fn($field) => "$field = :$field", $fields));

            // Agregar condición WHERE por UUID
            $sql = "UPDATE $table SET $setClause WHERE uuid = :uuid";

            // Agregar el UUID a los parámetros
            $data['uuid'] = $uuid;

            $stmt = $this->pdo->prepare($sql);
            $success = $stmt->execute($data);

            if (!$success) {
                throw new PDOException("No se pudo actualizar el registro.");
            }

            return ['result' => true, 'message' => 'Registro actualizado correctamente'];
        } catch (PDOException $e) {
            error_log("Error al actualizar el registro en {$table}: " . $e->getMessage());
            return ['result' => false, 'message' => $e->getMessage()];
        }
    }
}
