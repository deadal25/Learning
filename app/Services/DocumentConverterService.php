<?php

namespace App\Services;

use App\Models\Material;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DocumentConverterService
{
    /**
     * Get python executable path.
     */
    public static function getPythonPath(): string
    {
        $defaultWin = 'C:\\Users\\USER\\AppData\\Local\\Programs\\Python\\Python310\\python.exe';
        if (file_exists($defaultWin)) {
            return $defaultWin;
        }
        return 'python';
    }

    /**
     * Ensure the material has rendered slide images and PDF for browser viewing.
     *
     * @param Material $material
     * @param bool $force
     * @return array
     */
    public static function ensureConverted(Material $material, bool $force = false): array
    {
        if (!$material->file_path || !Storage::disk('public')->exists($material->file_path)) {
            return [
                'has_slides' => false,
                'slides' => [],
                'total_slides' => 0,
                'pdf_url' => null,
            ];
        }

        $materialId = $material->id;
        $relativeSlidesDir = "materials/slides_{$materialId}";
        $absSlidesDir = Storage::disk('public')->path($relativeSlidesDir);

        $relativePdf = "materials/converted_{$materialId}.pdf";
        $absConvertedPdf = Storage::disk('public')->path($relativePdf);

        $absInputFile = Storage::disk('public')->path($material->file_path);

        if ($force) {
            Storage::disk('public')->deleteDirectory($relativeSlidesDir);
            Storage::disk('public')->delete($relativePdf);
        }

        // Check if slides already exist
        $existingSlides = self::getSortedSlideFiles($absSlidesDir);

        if (empty($existingSlides)) {
            Storage::disk('public')->makeDirectory($relativeSlidesDir);

            // Run conversion script
            $python = self::getPythonPath();
            $script = base_path('scripts/convert_presentation.py');

            if (file_exists($script) && file_exists($absInputFile)) {
                $hintedExt = $material->file_type;
                if (empty($hintedExt) && $material->file_name) {
                    $hintedExt = pathinfo($material->file_name, PATHINFO_EXTENSION);
                }

                $cmd = escapeshellarg($python) . ' ' .
                       escapeshellarg($script) . ' ' .
                       escapeshellarg($absInputFile) . ' ' .
                       escapeshellarg($absSlidesDir) . ' ' .
                       escapeshellarg($absConvertedPdf) . ' ' .
                       escapeshellarg($hintedExt ?? '');

                $output = [];
                $returnCode = 0;
                exec($cmd, $output, $returnCode);

                if ($returnCode !== 0) {
                    Log::warning("Presentation conversion warning for material #{$materialId}", [
                        'code' => $returnCode,
                        'output' => $output
                    ]);
                }

                // Re-scan
                $existingSlides = self::getSortedSlideFiles($absSlidesDir);
            }
        }

        $slideUrls = [];
        foreach ($existingSlides as $slideFile) {
            $slideUrls[] = asset("storage/{$relativeSlidesDir}/{$slideFile}");
        }

        $pdfUrl = null;
        if ($material->isPdf()) {
            $pdfUrl = route('admin.materials.preview', $material);
        } elseif (file_exists($absConvertedPdf)) {
            $pdfUrl = route('admin.materials.preview', $material);
        }

        return [
            'has_slides' => !empty($slideUrls),
            'slides' => $slideUrls,
            'total_slides' => count($slideUrls),
            'pdf_url' => $pdfUrl,
        ];
    }

    /**
     * Get natural-sorted slide image filenames from a directory.
     */
    protected static function getSortedSlideFiles(string $dir): array
    {
        if (!is_dir($dir)) {
            return [];
        }

        $files = scandir($dir);
        $slideFiles = [];

        foreach ($files as $f) {
            if ($f === '.' || $f === '..') continue;
            $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $slideFiles[] = $f;
            }
        }

        natsort($slideFiles);
        return array_values($slideFiles);
    }

    /**
     * Get the absolute path to viewable PDF for this material.
     */
    public static function getPdfPath(Material $material): ?string
    {
        if (!$material->file_path) {
            return null;
        }

        if ($material->isPdf()) {
            $path = Storage::disk('public')->path($material->file_path);
            return file_exists($path) ? $path : null;
        }

        $converted = Storage::disk('public')->path("materials/converted_{$material->id}.pdf");
        if (file_exists($converted)) {
            return $converted;
        }

        // Try ensuring converted
        self::ensureConverted($material);
        return file_exists($converted) ? $converted : null;
    }
}
