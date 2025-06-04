<?php

namespace App\Interfaces;

interface FileParserInterface{
    public function extractContent(string $filePath): array|string;
}