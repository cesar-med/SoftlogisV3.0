<?php
require __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Mpdf\Mpdf;

class DocumentsCreateHelper
{
    public static function createXlsxDownload(array $data, string $fileName = 'reporte.xlsx')
    {
        if (empty($data)) {
            throw new Exception('No hay datos para generar el Excel');
        }

        // Crear documento
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Encabezados
        $headers = array_keys($data[0]);
        $sheet->fromArray($headers, null, 'A1');

        // Filas
        $rows = array_map('array_values', $data);
        $sheet->fromArray($rows, null, 'A2');

        // Estilos básicos
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->getFont()->setBold(true);
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . $sheet->getHighestRow())
            ->getAlignment()->setHorizontal('center');

        // Headers para descarga directa
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');

        // Enviar el archivo directamente al navegador
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
