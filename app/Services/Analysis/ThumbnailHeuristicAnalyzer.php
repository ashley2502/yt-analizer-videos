<?php

namespace App\Services\Analysis;

/**
 * Local checks before / without AI vision.
 */
class ThumbnailHeuristicAnalyzer
{
    /**
     * @return array{
     *     score: int,
     *     checks: array<int, array{id: string, pass: bool, weight: int, message: string}>,
     *     meta: array<string, mixed>
     * }
     */
    public function analyze(string $absolutePath): array
    {
        $info = @getimagesize($absolutePath);

        if ($info === false) {
            return [
                'score' => 0,
                'checks' => [[
                    'id' => 'readable',
                    'pass' => false,
                    'weight' => 100,
                    'message' => 'No se pudo leer la imagen.',
                ]],
                'meta' => [],
            ];
        }

        [$width, $height] = $info;
        $ratio = $width > 0 ? $width / max($height, 1) : 0;
        $checks = [];

        $checks[] = [
            'id' => 'resolution',
            'pass' => $width >= 1280 && $height >= 720,
            'weight' => 15,
            'message' => $width >= 1280 && $height >= 720
                ? "Resolución OK ({$width}x{$height})."
                : "Resolución baja ({$width}x{$height}). Usa al menos 1280x720.",
        ];

        $isLandscape = $ratio >= 1.6 && $ratio <= 1.9;
        $checks[] = [
            'id' => 'aspect_ratio',
            'pass' => $isLandscape,
            'weight' => 15,
            'message' => $isLandscape
                ? 'Aspect ratio cercano a 16:9.'
                : sprintf('Aspect ratio %.2f. YouTube thumbnail ideal ≈ 16:9 (1.78).', $ratio),
        ];

        $letterbox = $this->detectLetterbox($absolutePath, $width, $height, $info[2]);
        $checks[] = [
            'id' => 'letterbox',
            'pass' => ! $letterbox['detected'],
            'weight' => 25,
            'message' => $letterbox['detected']
                ? 'Detectamos barras negras/letterbox. Mata el CTR: usa imagen full-bleed.'
                : 'Sin barras negras evidentes.',
        ];

        $checks[] = [
            'id' => 'file_weight',
            'pass' => filesize($absolutePath) > 20_000,
            'weight' => 10,
            'message' => filesize($absolutePath) > 20_000
                ? 'Archivo con detalle suficiente.'
                : 'Archivo muy liviano; puede verse pixelado o ser un placeholder.',
        ];

        $earned = 0;
        $possible = 0;
        foreach ($checks as $check) {
            $possible += $check['weight'];
            if ($check['pass']) {
                $earned += $check['weight'];
            }
        }

        $score = (int) round(($earned / max($possible, 1)) * 100);

        return [
            'score' => $score,
            'checks' => $checks,
            'meta' => [
                'width' => $width,
                'height' => $height,
                'ratio' => round($ratio, 3),
                'letterbox' => $letterbox,
            ],
        ];
    }

    /**
     * @return array{detected: bool, top: float, bottom: float, left: float, right: float}
     */
    private function detectLetterbox(string $path, int $width, int $height, int $type): array
    {
        $image = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG => @imagecreatefrompng($path),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            default => false,
        };

        if ($image === false) {
            return ['detected' => false, 'top' => 0, 'bottom' => 0, 'left' => 0, 'right' => 0];
        }

        $sample = static function ($img, int $x, int $y): array {
            $rgb = imagecolorat($img, $x, $y);

            return [($rgb >> 16) & 0xFF, ($rgb >> 8) & 0xFF, $rgb & 0xFF];
        };

        $isDark = static function (array $rgb): bool {
            return ($rgb[0] + $rgb[1] + $rgb[2]) / 3 < 28;
        };

        $bandRatio = static function ($img, int $w, int $h, string $side) use ($sample, $isDark): float {
            $rows = max(1, (int) floor($h * 0.08));
            $cols = max(1, (int) floor($w * 0.08));
            $dark = 0;
            $total = 0;

            if ($side === 'top' || $side === 'bottom') {
                $yStart = $side === 'top' ? 0 : $h - $rows;
                for ($y = $yStart; $y < $yStart + $rows; $y++) {
                    for ($x = 0; $x < $w; $x += max(1, (int) floor($w / 40))) {
                        $total++;
                        if ($isDark($sample($img, min($x, $w - 1), min($y, $h - 1)))) {
                            $dark++;
                        }
                    }
                }
            } else {
                $xStart = $side === 'left' ? 0 : $w - $cols;
                for ($x = $xStart; $x < $xStart + $cols; $x++) {
                    for ($y = 0; $y < $h; $y += max(1, (int) floor($h / 40))) {
                        $total++;
                        if ($isDark($sample($img, min($x, $w - 1), min($y, $h - 1)))) {
                            $dark++;
                        }
                    }
                }
            }

            return $total > 0 ? $dark / $total : 0;
        };

        $top = $bandRatio($image, $width, $height, 'top');
        $bottom = $bandRatio($image, $width, $height, 'bottom');
        $left = $bandRatio($image, $width, $height, 'left');
        $right = $bandRatio($image, $width, $height, 'right');
        imagedestroy($image);

        $horizontalBars = $top > 0.82 && $bottom > 0.82;
        $verticalBars = $left > 0.82 && $right > 0.82;

        return [
            'detected' => $horizontalBars || $verticalBars,
            'top' => round($top, 2),
            'bottom' => round($bottom, 2),
            'left' => round($left, 2),
            'right' => round($right, 2),
        ];
    }
}
