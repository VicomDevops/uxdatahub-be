<?php

namespace App\Service\ServiceProviders;

use App\Interfaces\FileParserInterface;

class TxtFileParser implements FileParserInterface {
    public function extractContent(string $filePath): string|array {
        return file_get_contents($filePath);
    }
}