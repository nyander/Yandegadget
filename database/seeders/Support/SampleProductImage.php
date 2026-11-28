<?php

namespace Database\Seeders\Support;

use GdImage;
use RuntimeException;

/**
 * Draws a clean, clearly-labelled sample photo for demo products and items:
 * a simple illustration of the device on a soft background, with its name.
 * Local demo data only; real photos replace these. No downloads, so no
 * copyright questions and it works offline.
 */
final class SampleProductImage
{
    private const SIZE = 1200;

    /** Background colour pairs (top, bottom) per category slug. */
    private const BACKGROUNDS = [
        'phones' => ['#e0f2fe', '#bae6fd'],
        'computing' => ['#ede9fe', '#ddd6fe'],
        'tv-audio' => ['#fee2e2', '#fecaca'],
        'gaming' => ['#dcfce7', '#bbf7d0'],
        'cameras' => ['#fef3c7', '#fde68a'],
        'appliances' => ['#ffedd5', '#fed7aa'],
        'smart-tech' => ['#e0e7ff', '#c7d2fe'],
    ];

    /** Screen colours, so devices aren't all the same. */
    private const SCREENS = [
        ['#0ea5e9', '#6366f1'],
        ['#10b981', '#0ea5e9'],
        ['#f97316', '#ec4899'],
        ['#8b5cf6', '#ec4899'],
        ['#14b8a6', '#84cc16'],
    ];

    private GdImage $img;

    private string $font;

    private string $fontBold;

    private function __construct(private readonly int $seed)
    {
        $fonts = base_path('vendor/dompdf/dompdf/lib/fonts');
        $this->font = "{$fonts}/DejaVuSans.ttf";
        $this->fontBold = "{$fonts}/DejaVuSans-Bold.ttf";

        $img = imagecreatetruecolor(self::SIZE, self::SIZE);

        if ($img === false) {
            throw new RuntimeException('Could not create image.');
        }

        $this->img = $img;
        imagealphablending($this->img, true);
        imageantialias($this->img, true);
    }

    /**
     * A product photo for the shop gallery. Returns the path of a JPEG in the temp folder.
     */
    public static function product(string $categorySlug, string $name, string $brand, int $variant = 0): string
    {
        $image = new self(crc32($name) + $variant);
        $image->background($categorySlug, $variant);
        $image->device(self::kind($categorySlug, $name), 1.0, $variant, scratches: 0);
        $image->caption($brand, $name);

        return $image->save();
    }

    /**
     * A photo of one particular item: the same device with its grade and reference,
     * and a few light marks on the lower grades.
     */
    public static function item(string $categorySlug, string $name, string $reference, string $grade, string $gradeName): string
    {
        $image = new self(crc32($reference));
        $image->background($categorySlug, 1);
        $scratches = match ($grade) {
            'A' => 0,
            'B' => 2,
            'C' => 5,
            default => 9,
        };
        $image->device(self::kind($categorySlug, $name), 0.9, 1, $scratches);
        $image->badge("Grade {$grade} · {$gradeName}");
        $image->caption($reference, $name);

        return $image->save();
    }

    private static function kind(string $categorySlug, string $name): string
    {
        $lower = mb_strtolower($name);

        return match (true) {
            str_contains($lower, 'soundbar') => 'soundbar',
            str_contains($lower, 'buds') || str_contains($lower, 'airpods') || str_contains($lower, 'earbuds') => 'earbuds',
            str_contains($lower, 'switch') => 'handheld',
            str_contains($lower, 'watch') => 'watch',
            $categorySlug === 'phones' => 'phone',
            $categorySlug === 'computing' => 'laptop',
            $categorySlug === 'tv-audio' => 'tv',
            $categorySlug === 'gaming' => 'console',
            $categorySlug === 'cameras' => 'camera',
            $categorySlug === 'appliances' => 'coffee',
            $categorySlug === 'smart-tech' => 'watch',
            default => 'box',
        };
    }

    private function background(string $categorySlug, int $variant): void
    {
        [$top, $bottom] = self::BACKGROUNDS[$categorySlug] ?? ['#f3f4f6', '#e5e7eb'];

        if ($variant % 2 === 1) {
            [$top, $bottom] = [$bottom, $top];
        }

        [$r1, $g1, $b1] = $this->rgb($top);
        [$r2, $g2, $b2] = $this->rgb($bottom);

        for ($y = 0; $y < self::SIZE; $y++) {
            $t = $y / self::SIZE;
            $colour = $this->mix([$r1, $g1, $b1], [$r2, $g2, $b2], $t);
            imageline($this->img, 0, $y, self::SIZE, $y, $colour);
        }

        // A soft spotlight behind the device.
        $glow = $this->colour('#ffffff', 90);
        imagefilledellipse($this->img, 600, 520, 900, 900, $glow);

        // "Sample photo" label, so nobody mistakes it for the real item.
        $label = 'SAMPLE PHOTO';
        $box = imagettfbbox(26, 0, $this->fontBold, $label) ?: [0, 0, 260, 0];
        $this->roundedRect(48, 48, 48 + ($box[2] - $box[0]) + 64, 116, 34, $this->colour('#1f2937'));
        imagettftext($this->img, 26, 0, 80, 96, $this->colour('#ffffff'), $this->fontBold, $label);
    }

    private function device(string $kind, float $scale, int $variant, int $scratches): void
    {
        [$screenTop, $screenBottom] = self::SCREENS[($this->seed + $variant) % count(self::SCREENS)];
        $dark = $this->colour('#1f2937');
        $darker = $this->colour('#111827');
        $silver = $this->colour('#d1d5db');
        $white = $this->colour('#f9fafb');
        $s = fn (float $v) => (int) round($v * $scale);
        $cx = 600;
        $cy = 520;

        // Shadow on the "floor".
        imagefilledellipse($this->img, $cx, $cy + $s(420), $s(700), $s(70), $this->colour('#000000', 105));

        switch ($kind) {
            case 'phone':
                $this->roundedRect($cx - $s(210), $cy - $s(390), $cx + $s(210), $cy + $s(390), $s(60), $dark);
                $this->gradientRect($cx - $s(185), $cy - $s(360), $cx + $s(185), $cy + $s(360), $screenTop, $screenBottom, $s(42));
                $this->roundedRect($cx - $s(70), $cy - $s(350), $cx + $s(70), $cy - $s(318), $s(16), $darker);
                $this->roundedRect($cx - $s(80), $cy + $s(330), $cx + $s(80), $cy + $s(340), $s(5), $this->colour('#ffffff', 60));
                break;

            case 'laptop':
                $this->roundedRect($cx - $s(380), $cy - $s(330), $cx + $s(380), $cy + $s(150), $s(28), $dark);
                $this->gradientRect($cx - $s(350), $cy - $s(300), $cx + $s(350), $cy + $s(120), $screenTop, $screenBottom, $s(8));
                imagefilledpolygon($this->img, [
                    $cx - $s(430), $cy + $s(170),
                    $cx + $s(430), $cy + $s(170),
                    $cx + $s(480), $cy + $s(230),
                    $cx - $s(480), $cy + $s(230),
                ], $silver);
                $this->roundedRect($cx - $s(90), $cy + $s(170), $cx + $s(90), $cy + $s(188), $s(8), $this->colour('#9ca3af'));
                break;

            case 'tv':
                $this->roundedRect($cx - $s(460), $cy - $s(300), $cx + $s(460), $cy + $s(230), $s(16), $darker);
                $this->gradientRect($cx - $s(440), $cy - $s(280), $cx + $s(440), $cy + $s(210), $screenTop, $screenBottom, $s(6));
                imagefilledrectangle($this->img, $cx - $s(20), $cy + $s(230), $cx + $s(20), $cy + $s(330), $dark);
                $this->roundedRect($cx - $s(200), $cy + $s(325), $cx + $s(200), $cy + $s(350), $s(10), $dark);
                break;

            case 'soundbar':
                $this->roundedRect($cx - $s(480), $cy - $s(70), $cx + $s(480), $cy + $s(70), $s(60), $darker);
                for ($x = $cx - $s(420); $x <= $cx + $s(420); $x += $s(30)) {
                    for ($y = $cy - $s(30); $y <= $cy + $s(30); $y += $s(30)) {
                        imagefilledellipse($this->img, $x, $y, $s(10), $s(10), $this->colour('#4b5563'));
                    }
                }
                $this->roundedRect($cx + $s(160), $cy + $s(120), $cx + $s(440), $cy + $s(400), $s(30), $dark);
                imagefilledellipse($this->img, $cx + $s(300), $cy + $s(260), $s(170), $s(170), $this->colour('#374151'));
                break;

            case 'console':
                $this->roundedRect($cx - $s(170), $cy - $s(380), $cx + $s(170), $cy + $s(380), $s(90), $white);
                $this->roundedRect($cx - $s(60), $cy - $s(380), $cx + $s(60), $cy + $s(380), $s(30), $darker);
                $this->roundedRect($cx + $s(220), $cy + $s(220), $cx + $s(420), $cy + $s(330), $s(50), $white);
                imagefilledellipse($this->img, $cx + $s(270), $cy + $s(275), $s(30), $s(30), $dark);
                imagefilledellipse($this->img, $cx + $s(370), $cy + $s(275), $s(30), $s(30), $dark);
                break;

            case 'handheld':
                $this->roundedRect($cx - $s(330), $cy - $s(190), $cx + $s(330), $cy + $s(190), $s(30), $darker);
                $this->gradientRect($cx - $s(290), $cy - $s(160), $cx + $s(290), $cy + $s(160), $screenTop, $screenBottom, $s(10));
                $this->roundedRect($cx - $s(470), $cy - $s(190), $cx - $s(330), $cy + $s(190), $s(60), $this->colour('#06b6d4'));
                $this->roundedRect($cx + $s(330), $cy - $s(190), $cx + $s(470), $cy + $s(190), $s(60), $this->colour('#ef4444'));
                imagefilledellipse($this->img, $cx - $s(400), $cy - $s(80), $s(50), $s(50), $darker);
                imagefilledellipse($this->img, $cx + $s(400), $cy + $s(60), $s(50), $s(50), $darker);
                break;

            case 'earbuds':
                // An open charging case with a bud either side.
                $this->roundedRect($cx - $s(230), $cy - $s(40), $cx + $s(230), $cy + $s(300), $s(120), $white);
                $this->roundedRect($cx - $s(230), $cy - $s(40), $cx + $s(230), $cy + $s(10), $s(20), $silver);
                imagefilledellipse($this->img, $cx, $cy + $s(120), $s(40), $s(40), $this->colour('#10b981'));
                foreach ([-1, 1] as $side) {
                    $bx = $cx + $side * $s(150);
                    imagefilledellipse($this->img, $bx, $cy - $s(230), $s(190), $s(190), $darker);
                    imagefilledellipse($this->img, $bx, $cy - $s(230), $s(110), $s(110), $this->colour($screenTop));
                    $this->roundedRect($bx - $s(30), $cy - $s(200), $bx + $s(30), $cy - $s(60), $s(30), $darker);
                }
                break;

            case 'camera':
                $this->roundedRect($cx - $s(380), $cy - $s(200), $cx + $s(380), $cy + $s(250), $s(50), $dark);
                $this->roundedRect($cx - $s(130), $cy - $s(290), $cx + $s(130), $cy - $s(180), $s(30), $dark);
                imagefilledellipse($this->img, $cx, $cy + $s(30), $s(380), $s(380), $darker);
                imagefilledellipse($this->img, $cx, $cy + $s(30), $s(300), $s(300), $this->colour('#374151'));
                imagefilledellipse($this->img, $cx, $cy + $s(30), $s(190), $s(190), $this->colour($screenTop));
                imagefilledellipse($this->img, $cx - $s(35), $cy - $s(5), $s(50), $s(50), $this->colour('#ffffff', 50));
                $this->roundedRect($cx - $s(330), $cy - $s(170), $cx - $s(240), $cy - $s(130), $s(10), $this->colour('#ef4444'));
                break;

            case 'coffee':
                $this->roundedRect($cx - $s(220), $cy - $s(330), $cx + $s(220), $cy + $s(330), $s(60), $dark);
                imagefilledellipse($this->img, $cx, $cy - $s(230), $s(330), $s(130), $this->colour('#6b7280'));
                $this->roundedRect($cx - $s(120), $cy + $s(60), $cx + $s(120), $cy + $s(240), $s(20), $white);
                imagefilledellipse($this->img, $cx, $cy + $s(62), $s(240), $s(40), $this->colour('#78350f'));
                $this->roundedRect($cx - $s(200), $cy + $s(260), $cx + $s(200), $cy + $s(300), $s(14), $silver);
                break;

            case 'watch':
                $this->roundedRect($cx - $s(110), $cy - $s(420), $cx + $s(110), $cy + $s(420), $s(50), $this->colour('#475569'));
                $this->roundedRect($cx - $s(190), $cy - $s(230), $cx + $s(190), $cy + $s(230), $s(90), $darker);
                $this->gradientRect($cx - $s(160), $cy - $s(200), $cx + $s(160), $cy + $s(200), $screenTop, $screenBottom, $s(70));
                $this->roundedRect($cx + $s(188), $cy - $s(90), $cx + $s(215), $cy - $s(20), $s(10), $silver);
                break;

            default:
                $this->roundedRect($cx - $s(300), $cy - $s(300), $cx + $s(300), $cy + $s(300), $s(40), $this->colour('#9ca3af'));
        }

        // Light marks for lower grades, placed the same way every time for the same item.
        mt_srand($this->seed);
        for ($i = 0; $i < $scratches; $i++) {
            $x = $cx + mt_rand(-$s(260), $s(260));
            $y = $cy + mt_rand(-$s(260), $s(260));
            imagesetthickness($this->img, 3);
            imageline($this->img, $x, $y, $x + mt_rand(30, 110), $y + mt_rand(-25, 25), $this->colour('#ffffff', 55));
        }
        imagesetthickness($this->img, 1);
        mt_srand();
    }

    private function badge(string $text): void
    {
        $box = imagettfbbox(28, 0, $this->fontBold, $text) ?: [0, 0, 300, 0];
        $width = $box[2] - $box[0] + 64;
        $x = self::SIZE - 48 - $width;
        $this->roundedRect($x, 48, self::SIZE - 48, 116, 34, $this->colour('#047857'));
        imagettftext($this->img, 28, 0, $x + 32, 96, $this->colour('#ffffff'), $this->fontBold, $text);
    }

    private function caption(string $top, string $name): void
    {
        $this->centredText($top, 30, 1010, $this->font, $this->colour('#4b5563'));
        $this->centredText($name, mb_strlen($name) > 24 ? 44 : 54, 1090, $this->fontBold, $this->colour('#111827'));
    }

    private function centredText(string $text, int $size, int $baseline, string $font, int $colour): void
    {
        $box = imagettfbbox($size, 0, $font, $text) ?: [0, 0, 0, 0];
        $x = (int) ((self::SIZE - ($box[2] - $box[0])) / 2);
        imagettftext($this->img, $size, 0, $x, $baseline, $colour, $font, $text);
    }

    private function gradientRect(int $x1, int $y1, int $x2, int $y2, string $from, string $to, int $radius): void
    {
        // Draw the gradient onto its own canvas, then copy it through a rounded mask.
        [$r1, $g1, $b1] = $this->rgb($from);
        [$r2, $g2, $b2] = $this->rgb($to);
        $height = max(1, $y2 - $y1);

        for ($y = $y1; $y <= $y2; $y++) {
            $t = ($y - $y1) / $height;
            $colour = $this->mix([$r1, $g1, $b1], [$r2, $g2, $b2], $t);
            $inset = $this->cornerInset($y - $y1, $y2 - $y, $radius);
            imageline($this->img, $x1 + $inset, $y, $x2 - $inset, $y, $colour);
        }

        // A soft reflection across the top of the screen.
        imagefilledpolygon($this->img, [
            $x1 + $radius, $y1 + 4,
            $x1 + (int) (($x2 - $x1) * 0.55), $y1 + 4,
            $x1 + (int) (($x2 - $x1) * 0.25), $y1 + (int) (($y2 - $y1) * 0.45),
            $x1 + 4, $y1 + (int) (($y2 - $y1) * 0.45),
        ], $this->colour('#ffffff', 110));
    }

    /** How far a rounded corner pulls a row in, given the distance from the top and bottom edges. */
    private function cornerInset(int $fromTop, int $fromBottom, int $radius): int
    {
        $d = min($fromTop, $fromBottom);

        if ($radius <= 0 || $d >= $radius) {
            return 0;
        }

        return (int) round($radius - sqrt($radius ** 2 - ($radius - $d) ** 2));
    }

    private function roundedRect(int $x1, int $y1, int $x2, int $y2, int $radius, int $colour): void
    {
        $radius = max(0, min($radius, (int) (($x2 - $x1) / 2), (int) (($y2 - $y1) / 2)));
        imagefilledrectangle($this->img, $x1 + $radius, $y1, $x2 - $radius, $y2, $colour);
        imagefilledrectangle($this->img, $x1, $y1 + $radius, $x2, $y2 - $radius, $colour);

        foreach ([[$x1 + $radius, $y1 + $radius], [$x2 - $radius, $y1 + $radius], [$x1 + $radius, $y2 - $radius], [$x2 - $radius, $y2 - $radius]] as [$x, $y]) {
            imagefilledellipse($this->img, $x, $y, $radius * 2, $radius * 2, $colour);
        }
    }

    /**
     * @return array{0: int<0, 255>, 1: int<0, 255>, 2: int<0, 255>}
     */
    private function rgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        $part = fn (int $at): int => max(0, min(255, (int) hexdec(substr($hex, $at, 2))));

        return [$part(0), $part(2), $part(4)];
    }

    /** A colour; $alpha from 0 (solid) to 127 (clear). */
    private function colour(string $hex, int $alpha = 0): int
    {
        [$r, $g, $b] = $this->rgb($hex);

        return (int) imagecolorallocatealpha($this->img, $r, $g, $b, max(0, min(127, $alpha)));
    }

    /**
     * The colour $t of the way from one RGB colour to another.
     *
     * @param  array{0: int, 1: int, 2: int}  $from
     * @param  array{0: int, 1: int, 2: int}  $to
     */
    private function mix(array $from, array $to, float $t): int
    {
        $channel = fn (int $i): int => max(0, min(255, (int) round($from[$i] + ($to[$i] - $from[$i]) * $t)));

        return (int) imagecolorallocate($this->img, $channel(0), $channel(1), $channel(2));
    }

    private function save(): string
    {
        $path = tempnam(sys_get_temp_dir(), 'sample-').'.jpg';
        imagejpeg($this->img, $path, 86);

        return $path;
    }
}
