<?php

namespace App\Providers;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

class WebSitesImagesProvider
{
    public static function findImageWithSpecificDimensions(string $url, int $desiredWidth, int $desiredHeight): ?string
    {
        $htmlContent = self::fetchWebsiteContent($url);
        if (!$htmlContent) {
            return 'Failed to fetch website content';
        }

        $crawler = new Crawler($htmlContent);
        $images = $crawler->filter('img')->extract(['src']);

        foreach ($images as $imageUrl) {
            $absoluteUrl = self::resolveUrl($url, $imageUrl);
            [$width, $height] = self::getImageDimensions($absoluteUrl);

            if ($width === $desiredWidth && $height === $desiredHeight) {
                return $absoluteUrl;
            }
        }

        return null;
    }

    private static function fetchWebsiteContent(string $url): ?string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.9',
            'Cache-Control: no-cache',
        ]);
        $html = curl_exec($ch);
        curl_close($ch);

        return $html ?: null;
    }

    private static function resolveUrl(string $baseUrl, string $relativeUrl): string
    {
        if (filter_var($relativeUrl, FILTER_VALIDATE_URL)) {
            return $relativeUrl;
        }
        return rtrim($baseUrl, '/') . '/' . ltrim($relativeUrl, '/');
    }

    private static function getImageDimensions(string $imageUrl): array|string
    {
        try {
            $imageData = @file_get_contents($imageUrl);
            if ($imageData) {
                $imageInfo = @getimagesizefromstring($imageData);
                if ($imageInfo) {
                    return [$imageInfo[0], $imageInfo[1]];
                }
            }
        } catch (\Exception $e) {
            return 'Failed to fetch website content';
        }
        return [0, 0];
    }

    public static function imageToBase64(string $imageUrl): string|null
    {
        try {
            $imageData = file_get_contents($imageUrl);
            if ($imageData === false) {
                return null;
            }
            $imageInfo = getimagesizefromstring($imageData);
            if ($imageInfo === false) {
                return null;
            }
            return base64_encode($imageData);
        } catch (\Exception $e) {
            return null;
        }
    }
}