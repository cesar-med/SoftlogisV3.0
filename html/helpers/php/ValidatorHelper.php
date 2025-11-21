<?php
include_once __DIR__ . "/../../connection/core.php";
class ValidatorHelper
{
    private $coreDB;
    public function __construct()
    {
        $this->coreDB = new Database("authenticate");
    }

    public function validateData(array $data, array $rules)
    {
        $errors = [];
        $count = 0;

        foreach ($rules as $field => $ruleSet) {
            $value = trim($data[$field] ?? '');

            foreach ($ruleSet as $rule => $ruleValue) {
                switch ($rule) {
                    case 'required':
                        if ($ruleValue && $value === '') {
                            $errors[$field][] = 'Este campo es obligatorio.';
                        }
                        break;

                    case 'type':
                        if ($value !== '') {
                            switch ($ruleValue) {
                                case 'int':
                                    if (!filter_var($value, FILTER_VALIDATE_INT)) {
                                        $errors[$field][] = 'Debe ser un número entero.';
                                    }
                                    break;
                                case 'float':
                                    if (!filter_var($value, FILTER_VALIDATE_FLOAT)) {
                                        $errors[$field][] = 'Debe ser un número decimal.';
                                    }
                                    break;
                                case 'uuid':
                                    if (!preg_match('/^[a-f0-9\-]{36}$/i', $value)) {
                                        $errors[$field][] = 'Debe ser un UUID válido.';
                                    }
                                    break;
                                case 'email':
                                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                                        $errors[$field][] = 'Debe ser un correo electrónico válido.';
                                    }
                                    break;
                                case 'phone':
                                    if (!preg_match('/^\d{10}$/', $value)) {
                                        $errors[$field][] = 'Debe contener exactamente 10 dígitos numéricos.';
                                    }
                                    break;
                            }
                        }
                        break;

                    case 'max':
                        if (strlen($value) > $ruleValue) {
                            $errors[$field][] = "No debe tener más de $ruleValue caracteres.";
                        }
                        break;

                    case 'min':
                        if (strlen($value) < $ruleValue) {
                            $errors[$field][] = "Debe tener al menos $ruleValue caracteres.";
                        }
                        break;

                    case 'in':
                        if (!in_array($value, $ruleValue)) {
                            $errors[$field][] = 'Valor no permitido.';
                        }
                        break;

                    case 'unique':
                        if ($value !== '') {
                            // Separar tabla.columna y (opcionalmente) el ID a excluir
                            [$tableColumn, $exceptId] = array_pad(explode(',', $ruleValue), 2, null);
                            [$tabla, $columna] = explode('.', $tableColumn);

                            $sql = "SELECT COUNT(*) as total FROM `$tabla` WHERE `$columna` = :value";

                            // Si se pasa un ID a excluir, añadir condición
                            if ($exceptId !== null) {
                                $sql .= " AND uuid != :exceptId";
                            }

                            $params = [':value' => $value];

                            if ($exceptId !== null) {
                                $params[':exceptId'] =  $exceptId;
                            }

                            $fetch = $this->coreDB->readDb($sql, $params);

                            if ($fetch['result'] && $fetch['data'][0]['total'] > 0) {
                                $errors[$field][] = "Este valor ya existe";
                            };
                        }
                        break;

                    case 'uniqueDual':
                        if ($value !== '') {
                            // Separar tabla.columna y (opcionalmente) el ID a excluir
                            [$tabla, $columna, $parent, $parentValue] = $ruleValue;

                            $sql = "SELECT COUNT(*) FROM `$tabla` WHERE `$columna` = :value and $parent=:parentValue";
                            $params = [':value' => $value, ':parentValue' => $parentValue];


                            $fetch = $this->coreDB->readDb($sql, $params);

                            if ($fetch['result'] && $fetch['data'][0]['total'] > 0) {
                                $errors[$field][] = "Este valor ya existe";
                            };
                        }
                        break;

                    case 'uniqueConcat':
                        if ($value !== '') {

                            [$table, $field1, $field2, $value1, $value2] = $ruleValue;

                            $sql = "SELECT COUNT(*) as total FROM $table WHERE $field1 = :val1 AND $field2 = :val2";
                            $params = ['val1' => $value1, 'val2' => $value2];

                            $fetch = $this->coreDB->readDb($sql, $params);

                            if ($fetch['result'] && $fetch['data'][0]['total'] > 0) {
                                $errors[$field][] = "Este valor ya existe";
                            };
                        }

                        break;
                    case 'password':
                        if (
                            $ruleValue === false && $value !== '' &&
                            !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $value)
                        ) {
                            $errors[$field][] = 'La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula, un número y un símbolo.';
                        }
                        break;

                    case 'matches':
                        if ($value !== ($data[$ruleValue] ?? '')) {
                            $errors[$field][] = 'Los campos no coinciden.';
                        }
                        break;
                }
            }
        }

        return $errors;
    }
    public function validatorUUID($uuid)
    {
        if (!$uuid || empty(trim($uuid))) {
            return false;
        }

        return preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $uuid
        ) === 1;
    }
    public function generateUUIDv4()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0x0fff) | 0x4000,
            random_int(0, 0x3fff) | 0x8000,
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0xffff)
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
}
