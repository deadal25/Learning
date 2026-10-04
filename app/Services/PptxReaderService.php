<?php

namespace App\Services;

use ZipArchive;

class PptxReaderService
{
    /**
     * Parse a PPTX file and return an array of slides with text and images.
     *
     * @param string $absoluteFilePath
     * @return array
     */
    public static function parse(string $absoluteFilePath): array
    {
        if (!file_exists($absoluteFilePath)) {
            return [
                'success' => false,
                'error' => 'File tidak ditemukan.',
                'total_slides' => 0,
                'slides' => [],
            ];
        }

        $zip = new ZipArchive();
        if ($zip->open($absoluteFilePath) !== true) {
            return [
                'success' => false,
                'error' => 'Tidak dapat membuka format PPTX.',
                'total_slides' => 0,
                'slides' => [],
            ];
        }

        $slideFiles = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (preg_match('#^ppt/slides/slide(\d+)\.xml$#', $name, $matches)) {
                $slideFiles[(int)$matches[1]] = $name;
            }
        }
        ksort($slideFiles);

        $parsedSlides = [];
        $slideIndex = 1;

        foreach ($slideFiles as $num => $slideXmlPath) {
            $xmlContent = $zip->getFromName($slideXmlPath);
            if (!$xmlContent) continue;

            // 1. Get slide relationships to find images
            $relsXmlPath = "ppt/slides/_rels/slide{$num}.xml.rels";
            $imageMap = [];
            if ($zip->locateName($relsXmlPath) !== false) {
                $relsContent = $zip->getFromName($relsXmlPath);
                if (preg_match_all('#<Relationship[^>]*Id="([^"]+)"[^>]*Type="[^"]*image"[^>]*Target="([^"]+)"#i', $relsContent, $relMatches, PREG_SET_ORDER)) {
                    foreach ($relMatches as $rm) {
                        $rId = $rm[1];
                        $target = $rm[2];
                        // Normalize target path (e.g. "../media/image1.png" -> "ppt/media/image1.png")
                        $mediaPath = 'ppt/' . ltrim(str_replace('../', '', $target), '/');
                        if ($zip->locateName($mediaPath) !== false) {
                            $mediaData = $zip->getFromName($mediaPath);
                            $ext = strtolower(pathinfo($mediaPath, PATHINFO_EXTENSION));
                            $mime = match ($ext) {
                                'png' => 'image/png',
                                'jpg', 'jpeg' => 'image/jpeg',
                                'gif' => 'image/gif',
                                'svg' => 'image/svg+xml',
                                default => 'application/octet-stream',
                            };
                            $base64 = 'data:' . $mime . ';base64,' . base64_encode($mediaData);
                            $imageMap[$rId] = $base64;
                        }
                    }
                }
            }

            // 2. Extract paragraphs and text
            // Strip XML namespaces for easy parsing
            $cleanXml = preg_replace('/(<\/?)(\w+):([^>]*>)/', '$1$3', $xmlContent);
            $lines = [];
            $title = null;

            if ($sxml = @simplexml_load_string($cleanXml)) {
                $paragraphs = $sxml->xpath('//p');
                foreach ($paragraphs as $p) {
                    $texts = $p->xpath('.//t');
                    $lineStr = '';
                    foreach ($texts as $t) {
                        $lineStr .= (string)$t;
                    }
                    $lineStr = trim($lineStr);
                    if ($lineStr !== '') {
                        if ($title === null) {
                            $title = $lineStr;
                        } else {
                            $lines[] = $lineStr;
                        }
                    }
                }
            } else {
                // Regex fallback
                preg_match_all('#<a:t[^>]*>(.*?)</a:t>#su', $xmlContent, $tMatches);
                $rawTexts = array_filter(array_map('trim', $tMatches[1]));
                if (!empty($rawTexts)) {
                    $title = array_shift($rawTexts);
                    $lines = array_values($rawTexts);
                }
            }

            // 3. Find images used on this slide
            $slideImages = [];
            if (preg_match_all('#blip[^>]*r:embed="([^"]+)"#i', $xmlContent, $embedMatches)) {
                foreach ($embedMatches[1] as $rId) {
                    if (isset($imageMap[$rId])) {
                        $slideImages[] = $imageMap[$rId];
                    }
                }
            }
            // If none found by r:embed, include all images mapped to this slide
            if (empty($slideImages) && !empty($imageMap)) {
                $slideImages = array_values($imageMap);
            }

            $parsedSlides[$slideIndex] = [
                'slide_number' => $slideIndex,
                'title' => $title ?? "Slide {$slideIndex}",
                'lines' => $lines,
                'images' => $slideImages,
            ];

            $slideIndex++;
        }

        $zip->close();

        return [
            'success' => true,
            'total_slides' => count($parsedSlides),
            'slides' => $parsedSlides,
        ];
    }
}
