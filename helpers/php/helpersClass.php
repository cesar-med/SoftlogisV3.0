<?php
require_once __DIR__ . "/../../connections/core.php";
require __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Mpdf\Mpdf;

class HelpersClass
{

    private $pdo;
    public function __construct(string $db = "catalogs")
    {
        $this->pdo = Database::getInstance($db);
    }

    public function logAction($pdo, $userId, $action, $module, $data = [], $reference = null, $message = "")
    {
        $stmt = $pdo->prepare("
        INSERT INTO auditorias.loggers 
        (user_id, action, module, data_json, reference,message)
        VALUES (:user_id, :action, :module, :data_json, :reference,:message)
    ");

        $send = $stmt->execute([
            'user_id' => $userId,
            'action' => $action,
            'module' => $module,
            'data_json' => json_encode($data),
            'reference' => $reference,
            'message' => $message
        ]);

        if ($send) {
            return ['result' => true];
        } else {
            return ['result' => false];
        }
    }
    public function insertSubcatalogs(array $request)
    {

        $tableMap = [
            'activities' => ['activities', 'description'],
            'activity_categories' => ['activity_categories', 'name'],
            'activity_subcategories' => ['activity_subcategories', 'name', 'category_id'],
            'supplier_categories' => ['supplier_categories', 'name'],
            'supplier_subcategories' => ['supplier_subcategories', 'name', 'category_id'],
            'truck_brands' => ['truck_brands', 'name'],
            'truck_models' => ['truck_models', 'name'],
            'trailer_brands' => ['trailer_brands', 'name'],
            'trailer_models' => ['trailer_models', 'name'],
            'departments' => ['departments', 'name'],
            'employee_work_shifts' => ['employee_work_shifts', 'name'],
            'employee_positions' => ['employee_positions', 'name', 'department_id'],
            'material_categories' => ['material_categories', 'name'],
            'material_subcategories' => ['material_subcategories', 'name', 'category_id'],
            'material_types' => ['material_types', 'name', 'subcategory_id'],
            'material_units' => ['material_units', 'name'],
            'visitor_activities' => ['visitor_activities', 'name', '', false, 'seguridad']
        ];

        $userSession = $request['userSession']['userSession'] ?? null;
        $type = $request['type'];

        unset($request['userSession'], $request['type']);

        if (!isset($request['description']) || !isset($type) || !isset($tableMap[$type][0])) {
            return ['result' => false, 'message' => "Los datos no están completos"];
        }

        $rules = [];

        $data = [
            $tableMap[$type][1] => $request['description']
        ];

        if (isset($request['parent']) && isset($request['parentValue']) && !empty(trim($request['parent']))) {
            $rules['description'] = [
                'required' => true,
                'uniqueDual' => [
                    $tableMap[$type][0],
                    $tableMap[$type][1],
                    $tableMap[$type][2],
                    $request['parentValue']
                ]
            ];
            $data[$tableMap[$type][2]] = $request['parentValue'];
        } else {
            $rules['description'] = [
                'required' => true,
                'unique' => "{$tableMap[$type][0]}.{$tableMap[$type][1]}"
            ];
        }

        if (isset($tableMap[$type][3]) && !$tableMap[$type][3]) {
            $this->pdo = Database::getInstance($tableMap[$type][4]);
        }

        $errores = $this->validateData($request, $rules, $this->pdo);

        if (!empty($errores)) {
            return ['result' => false, 'message' => $errores];
        }

        $insert = $this->insert($data, $tableMap[$type][0]);

        return $insert;
    }
    public function getImagesCatalogs(string $name, string $type)
    {

        $selectedTable = [
            'products' => 'files.products',
            'clients' => 'clients.data',
            'users' => 'admin.users'
        ][$type] ?? null;

        if (!$selectedTable) {
            return false;
        }

        try {
            $stmt = $this->pdo->prepare("SELECT name,type,path FROM $selectedTable WHERE name=:name");
            $stmt->execute(['name' => $name]);

            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $data ? ['result' => true, 'data' => $data] : ['result' => false, 'message' => 'No hay datos'];
        } catch (PDOException $e) {
            return ['result' => false, 'message' => $e->getMessage()];
        }
    }
    public function generateUUIDv4()
    {
        // Genera un UUID versión 4 (aleatorio)
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),     // 32 bits
            mt_rand(0, 0xffff),                         // 16 bits
            mt_rand(0, 0x0fff) | 0x4000,                // versión 4
            mt_rand(0, 0x3fff) | 0x8000,                // variante 10xx
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff) // 48 bits
        );
    }
    public function buildWhereClause(array $filters, array $fieldMap, array &$params): string
    {
        $clauses = [];
        $index = 1;

        foreach ($filters as $key => $value) {
            if (!isset($fieldMap[$key])) continue;

            if ($key === 'search' && is_string($value)) {
                $searchValue = "%$value%";
                $searchClauses = [];
                $localFieldMap = $fieldMap; // copia local
                unset($localFieldMap['search']);

                foreach ($localFieldMap as $fieldConfig) {
                    if (!empty($fieldConfig['searchable'])) {
                        $paramName = ":search_value" . $index++;
                        $searchClauses[] = "{$fieldConfig['field']} LIKE $paramName";
                        $params[$paramName] = $searchValue;
                    }
                }

                if ($searchClauses) {
                    $clauses[] = '(' . implode(' OR ', $searchClauses) . ')';
                }
                continue;
            }

            $config = $fieldMap[$key];
            $field = $config['field'];
            $type = $config['type'] ?? 'string';
            $paramBase = preg_replace('/[^a-zA-Z0-9_]/', '_', $field);

            if ($value === null) {
                $clauses[] = "$field IS NULL";
                continue;
            }

            switch ($type) {
                case 'int':
                case 'string':
                    if (is_array($value)) {
                        $placeholders = [];
                        foreach ($value as $val) {
                            $param = ":{$paramBase}_{$index}";
                            $placeholders[] = $param;
                            $params[$param] = $val;
                            $index++;
                        }
                        $clauses[] = "$field IN (" . implode(', ', $placeholders) . ")";
                    } else {
                        $param = ":{$paramBase}_{$index}";
                        $clauses[] = "$field = $param";
                        $params[$param] = $value;
                        $index++;
                    }
                    break;

                case 'like':
                    $param = ":{$paramBase}_{$index}";
                    $clauses[] = "$field LIKE $param";
                    $params[$param] = "%$value%";
                    $index++;
                    break;

                case 'bool':
                    $param = ":{$paramBase}_{$index}";
                    $clauses[] = "$field = $param";
                    $params[$param] = $value ? 1 : 0;
                    $index++;
                    break;

                case 'between':
                    if (is_array($value) && count($value) === 2) {
                        [$start, $end] = $value;
                        $paramStart = ":{$paramBase}_start{$index}";
                        $paramEnd = ":{$paramBase}_end{$index}";
                        $clauses[] = "($field BETWEEN $paramStart AND $paramEnd)";
                        $params[$paramStart] = $start;
                        $params[$paramEnd] = $end;
                        $index++;
                    }
                    break;
            }
        }

        return $clauses ? 'WHERE ' . implode(' AND ', $clauses) : '';
    }

    public function createXlsxDownload(array $data)
    {

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $headers = array_keys($data[0]);
        $sheet->fromArray($headers, null, 'A1');

        // 2. Convertir los datos asociativos a arrays indexados
        $rows = array_map('array_values', $data);

        // 3. Insertar los datos en la fila 2 en adelante
        $sheet->fromArray($rows, null, 'A2');

        $writer = new Xlsx($spreadsheet);

        return $writer;
    }
    private function insert(array $data, string $table): array
    {
        try {
            $pdo = $this->pdo;

            // Si el nombre incluye un punto, asumimos que es "base.tabla"
            if (strpos($table, '.') !== false) {
                [$dbName, $tableName] = explode('.', $table, 2);

                // Reasignar la conexión a la base correcta
                $pdo = Database::getInstance($dbName);
                $table = $tableName; // OJO: quitamos el prefijo para el SQL
            }

            $pdo->beginTransaction();

            $fields = array_keys($data);
            $columns = implode(', ', $fields);
            $placeholders = ':' . implode(', :', $fields);

            $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
            $stmt = $pdo->prepare($sql);

            $send = $stmt->execute($data);

            if (!$send) {
                throw new PDOException("No se pudo insertar el registro en $table.");
            }

            $lastId = $pdo->lastInsertId();
            $pdo->commit();

            return ['result' => true, 'id' => $lastId];
        } catch (PDOException $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Error al insertar en $table: " . $e->getMessage());
            return ['result' => false, 'message' => $e->getMessage()];
        }
    }
    public function orderFilesArray(array $files): array
    {
        $arrayOrdenado = [];

        if (isset($files['name']) && is_array($files['name'])) {
            $totalArchivos = count($files['name']);

            for ($i = 0; $i < $totalArchivos; $i++) {
                $arrayOrdenado[] = [
                    'name' => $files['name'][$i],
                    'full_path' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'size' => $files['size'][$i],
                    'error' => $files['error'][$i],
                ];
            }
        }

        return $arrayOrdenado;
    }
    public static function generatePdf(string $html, string $outputName = 'document.pdf', string $mode = 'I')
    {
        $tempDir = __DIR__ . '/../../tmp/mpdf';
        $date = date('d-m-Y');
        if (!file_exists(filename: $tempDir)) {
            mkdir($tempDir, 0777, true); // crea la carpeta si no existe
        }

        $mpdf = new Mpdf([
            'margin_top'    => 15,
            'margin_bottom' => 15,
            'margin_left'   => 10,
            'margin_right'  => 10,
            'tempDir'       => $tempDir,
        ]);

        // Footer con HTML
        $mpdf->SetHTMLFooter("
            <table width='100%' style='font-size:10px; color:#64748b; border-top:1px solid #ccc; padding-top:5px;'>
                <tr>
                    <td width='33%' align='left'>Fecha de emisión: {$date}</td>
                    <td width='34%' align='center'>UMC-FO-MTO-001 Revisión 'A' </td>
                    <td width='33%' align='right'>Página {PAGENO} de {nb}</td>
                </tr>
            </table>
            ");
        $mpdf->WriteHTML($html);

        // $mode:
        // 'I' → inline en navegador
        // 'D' → descargar
        // 'F' → guardar en servidor
        // 'S' → devolver como string
        return $mpdf->Output($outputName, $mode);
    }
}
