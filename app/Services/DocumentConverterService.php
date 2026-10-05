<?php

namespace App\Services;

use App\Models\Material;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DocumentConverterService
{
    /**
     * Resolve absolute file path from either /tmp storage, project bundled storage, or public directory.
     */
    public static function resolveFilePath(?string $relativePath): ?string
    {
        if (empty($relativePath)) {
            return null;
        }

        // 1. Temporary storage (/tmp/storage/app/public)
        try {
            $tmpPath = Storage::disk('public')->path($relativePath);
            if (file_exists($tmpPath) && is_file($tmpPath)) {
                return $tmpPath;
            }
        } catch (\Throwable $e) {}

        // 2. Bundled repository storage (storage/app/public)
        $bundledPath = base_path('storage/app/public/' . $relativePath);
        if (file_exists($bundledPath) && is_file($bundledPath)) {
            return $bundledPath;
        }

        // 3. Public directory storage (public/storage)
        $pubPath = public_path('storage/' . $relativePath);
        if (file_exists($pubPath) && is_file($pubPath)) {
            return $pubPath;
        }

        return null;
    }

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
     * Check if a command is executable on the current host.
     */
    protected static function isCommandAvailable(string $cmd): bool
    {
        if (!function_exists('exec')) {
            return false;
        }
        if (str_contains($cmd, '\\') || str_contains($cmd, '/')) {
            return file_exists($cmd);
        }
        $testCmd = (PHP_OS_FAMILY === 'Windows') ? "where " . escapeshellarg($cmd) : "which " . escapeshellarg($cmd);
        $output = [];
        $code = 1;
        @exec($testCmd, $output, $code);
        return $code === 0;
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
        $resolvedInput = self::resolveFilePath($material->file_path);
        if (!$material->file_path || !$resolvedInput) {
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
        $bundledSlidesDir = base_path("storage/app/public/{$relativeSlidesDir}");

        $relativePdf = "materials/converted_{$materialId}.pdf";
        $absConvertedPdf = Storage::disk('public')->path($relativePdf);
        $bundledConvertedPdf = base_path("storage/app/public/{$relativePdf}");

        if ($force) {
            try {
                Storage::disk('public')->deleteDirectory($relativeSlidesDir);
                Storage::disk('public')->delete($relativePdf);
            } catch (\Throwable $e) {}
        }

        // Check if slides already exist in tmp or in bundled storage
        $existingSlides = self::getSortedSlideFiles($absSlidesDir);
        if (empty($existingSlides) && is_dir($bundledSlidesDir)) {
            $existingSlides = self::getSortedSlideFiles($bundledSlidesDir);
        }

        if (empty($existingSlides)) {
            try {
                @Storage::disk('public')->makeDirectory($relativeSlidesDir);
            } catch (\Throwable $e) {}

            // Run conversion script if Python environment is available
            $python = self::getPythonPath();
            $script = base_path('scripts/convert_presentation.py');

            if (file_exists($script) && file_exists($resolvedInput) && self::isCommandAvailable($python)) {
                $hintedExt = $material->file_type;
                if (empty($hintedExt) && $material->file_name) {
                    $hintedExt = pathinfo($material->file_name, PATHINFO_EXTENSION);
                }

                $cmd = escapeshellarg($python) . ' ' .
                       escapeshellarg($script) . ' ' .
                       escapeshellarg($resolvedInput) . ' ' .
                       escapeshellarg($absSlidesDir) . ' ' .
                       escapeshellarg($absConvertedPdf) . ' ' .
                       escapeshellarg($hintedExt ?? '');

                $output = [];
                $returnCode = 0;
                try {
                    @exec($cmd, $output, $returnCode);
                    if ($returnCode !== 0) {
                        Log::warning("Presentation conversion warning for material #{$materialId}", [
                            'code' => $returnCode,
                            'output' => $output
                        ]);
                    }
                } catch (\Throwable $e) {
                    Log::warning("Presentation conversion exception for material #{$materialId}: " . $e->getMessage());
                }

                // Re-scan
                $existingSlides = self::getSortedSlideFiles($absSlidesDir);
            }
        }

        $slideUrls = [];
        foreach ($existingSlides as $slideFile) {
            $slideUrls[] = asset("storage/{$relativeSlidesDir}/{$slideFile}");
        }

        // Dynamic PDF preview route based on student or staff
        $previewRoute = (auth()->check() && auth()->user()->isStudent())
            ? 'student.materials.preview'
            : 'admin.materials.preview';

        $pdfUrl = null;
        if ($material->isPdf()) {
            $pdfUrl = route($previewRoute, $material);
        } elseif (file_exists($absConvertedPdf) || file_exists($bundledConvertedPdf)) {
            $pdfUrl = route($previewRoute, $material);
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
            return self::resolveFilePath($material->file_path);
        }

        $convertedRel = "materials/converted_{$material->id}.pdf";
        $resolved = self::resolveFilePath($convertedRel);
        if ($resolved) {
            return $resolved;
        }

        // Try ensuring converted if not yet created
        self::ensureConverted($material);
        return self::resolveFilePath($convertedRel);
    }
}

