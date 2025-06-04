<?php

namespace App\Service\ServiceProviders;

use App\Interfaces\FileParserInterface;
use Exception;
use Smalot\PdfParser\Parser;

class PdfFileParser implements FileParserInterface {
    /**
     * @throws Exception
     */
    public function extractContent(string $filePath): string|array {
        $parser = new Parser();
        $pdf = $parser->parseFile($filePath);
        return $pdf->getText();
    }
}