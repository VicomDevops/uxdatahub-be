<?php

namespace App\Service\ServiceProviders;

use App\Interfaces\FileParserInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;

class XlsxFileParser implements FileParserInterface {
    public function extractContent(string $filePath): string|array
    {
        $spreadsheet = IOFactory::load($filePath);
        $data = [];
        $activeSheet = $spreadsheet->getActiveSheet();
        $firstRow = true;
        foreach ($activeSheet->getRowIterator() as $row) {
            if ($firstRow) {
                $firstRow = false;
                continue;
            }
            $cells = [];
            foreach ($row->getCellIterator() as $cell) {
                $cells[] = $cell->getValue();
            }
            $stepNumber = $cells[1] ?? '';
            if ($stepNumber) {
                $data["N° Etape ".$stepNumber][] = [
                    'test_name' => $cells[0] ?? '',
                    'tester_last_name' => $cells[2] ?? '',
                    'tester_first_name' => $cells[3] ?? '',
                    'tester_email' => $cells[4] ?? '',
                    'question_type' => $cells[5] ?? '',
                    'question' => $cells[6] ?? '',
                    'tester_response' => $cells[7] ?? '',
                    'comment' => $cells[8] ?? '',
                    'low_bound_label' => $cells[9] ?? '',
                    'high_bound_label' => $cells[10] ?? '',
                    'min' => $cells[11] ?? '',
                    'max' => $cells[12] ?? '',
                ];
            }
        }

        return $data;
    }
}