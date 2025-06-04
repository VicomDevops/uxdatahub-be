<?php

namespace App\Service\ServiceProviders;

use App\Interfaces\FileParserInterface;
use Exception;

class FileParserFactory {
    /**
     * @throws Exception
     */
    public static function create(string $fileExtension): FileParserInterface {
        switch ($fileExtension) {
            case 'txt':
                return new TxtFileParser();
            case 'pdf':
                return new PdfFileParser();
            case 'xlsx':
                return new XlsxFileParser();
            default:
                throw new Exception("Unsupported file type: " . $fileExtension);
        }
    }
}