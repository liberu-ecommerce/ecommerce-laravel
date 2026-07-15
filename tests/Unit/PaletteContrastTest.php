<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Guards the Workshop Moss palette (resources/css/app.css @theme) against the two
 * ways it can silently rot: a token drifting outside the sRGB gamut, or a
 * foreground/background pair dropping under WCAG 2.2 AA.
 *
 * PRODUCT.md commits to WCAG 2.2 AA, and DESIGN.md's Verified Palette Rule says
 * every value is machine-checked rather than eyeballed. This is that check — the
 * rule is only real because this test fails when someone edits a lightness.
 */
class PaletteContrastTest extends TestCase
{
    private const CSS = __DIR__.'/../../resources/css/app.css';

    /** @return array<string, array{float, float, float}> token => [L, C, H] */
    private function tokens(): array
    {
        $css = file_get_contents(self::CSS);

        // Only the @theme block defines the palette.
        preg_match('/@theme\s*\{(.+?)\n\}/s', $css, $block);
        $this->assertNotEmpty($block, '@theme block not found in app.css');

        preg_match_all(
            '/--color-([a-z0-9-]+):\s*oklch\(\s*([\d.]+)\s+([\d.]+)\s+([\d.]+)\s*\)/i',
            $block[1],
            $m,
            PREG_SET_ORDER
        );

        $tokens = [];
        foreach ($m as $t) {
            $tokens[$t[1]] = [(float) $t[2], (float) $t[3], (float) $t[4]];
        }

        return $tokens;
    }

    /** OKLCH -> linear sRGB. */
    private function toLinearRgb(float $L, float $C, float $H): array
    {
        $h = deg2rad($H);
        $a = $C * cos($h);
        $b = $C * sin($h);

        $l = ($L + 0.3963377774 * $a + 0.2158037573 * $b) ** 3;
        $m = ($L - 0.1055613458 * $a - 0.0638541728 * $b) ** 3;
        $s = ($L - 0.0894841775 * $a - 1.2914855480 * $b) ** 3;

        return [
            4.0767416621 * $l - 3.3077115913 * $m + 0.2309699292 * $s,
            -1.2684380046 * $l + 2.6097574011 * $m - 0.3413193965 * $s,
            -0.0041960863 * $l - 0.7034186147 * $m + 1.7076147010 * $s,
        ];
    }

    private function luminance(array $lch): float
    {
        [$r, $g, $b] = array_map(
            fn ($v) => max(0.0, min(1.0, $v)),
            $this->toLinearRgb(...$lch)
        );

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }

    private function contrast(array $fg, array $bg): float
    {
        $a = $this->luminance($fg);
        $b = $this->luminance($bg);
        [$hi, $lo] = $a > $b ? [$a, $b] : [$b, $a];

        return ($hi + 0.05) / ($lo + 0.05);
    }

    #[Test]
    public function every_palette_token_is_inside_the_srgb_gamut(): void
    {
        $tokens = $this->tokens();
        $this->assertNotEmpty($tokens, 'no oklch tokens parsed from @theme');

        foreach ($tokens as $name => $lch) {
            foreach ($this->toLinearRgb(...$lch) as $channel) {
                $this->assertGreaterThanOrEqual(
                    -0.0005,
                    $channel,
                    "--color-{$name} clips below the sRGB gamut; reduce its chroma"
                );
                $this->assertLessThanOrEqual(
                    1.0005,
                    $channel,
                    "--color-{$name} clips above the sRGB gamut; reduce its chroma"
                );
            }
        }
    }

    /** @return array<string, array{string, string, float}> */
    public static function contrastPairs(): array
    {
        // [foreground token, background token, required ratio]
        return [
            'body text on ground' => ['ink', null, 4.5],
            'secondary text on ground' => ['muted', null, 4.5],
            'brand text on ground' => ['primary-700', null, 4.5],
            'focus ring on ground (1.4.11)' => ['primary-600', null, 3.0],
            'control boundary on ground (1.4.11)' => ['stroke', null, 3.0],
            'error text on ground' => ['danger-600', null, 4.5],
            'body text on panel' => ['ink', 'surface', 4.5],
            'secondary text on panel' => ['muted', 'surface', 4.5],
            'success alert text on tint' => ['primary-700', 'primary-50', 4.5],
            'success icon on tint' => ['primary-600', 'primary-50', 3.0],
            'badge text on badge bg' => ['primary-700', 'primary-100', 4.5],
            'error alert text on tint' => ['danger-700', 'danger-50', 4.5],
            'error icon on tint' => ['danger-600', 'danger-50', 3.0],
            'warning alert text on tint' => ['warning-700', 'warning-50', 4.5],
            'warning icon on tint' => ['warning-600', 'warning-50', 3.0],
            'footer body on dark' => ['ink-inverse', 'primary-950', 4.5],
            'footer copyright on dark' => ['muted-inverse', 'primary-950', 4.5],
            // Disabled controls are exempt from 1.4.3, but a sold-out button is
            // information a shopper needs to read, so it is held to AA anyway.
            'disabled button label' => ['muted', 'hairline', 4.5],
        ];
    }

    /** A null background means the pure white ground (The White Ground Rule). */
    #[Test]
    #[DataProvider('contrastPairs')]
    public function palette_pair_meets_wcag_aa(string $fg, ?string $bg, float $required): void
    {
        $tokens = $this->tokens();
        $white = [1.0, 0.0, 0.0];

        $this->assertArrayHasKey($fg, $tokens, "--color-{$fg} is missing from @theme");
        if ($bg !== null) {
            $this->assertArrayHasKey($bg, $tokens, "--color-{$bg} is missing from @theme");
        }

        $ratio = $this->contrast($tokens[$fg], $bg === null ? $white : $tokens[$bg]);

        $this->assertGreaterThanOrEqual(
            $required,
            round($ratio, 2),
            sprintf(
                '%s on %s is %.2f:1, below the required %.1f:1',
                $fg,
                $bg ?? 'white ground',
                $ratio,
                $required
            )
        );
    }

    /** The White Ground Rule: the body ground is literal white, never a warm tint. */
    #[Test]
    public function the_ground_stays_pure_white(): void
    {
        $css = file_get_contents(dirname(self::CSS, 2).'/views/layouts/app.blade.php');

        $this->assertMatchesRegularExpression(
            '/<body[^>]*\bclass="[^"]*\bbg-white\b/',
            $css,
            'The body ground must be bg-white. Cream, sand and warm near-whites are banned by DESIGN.md.'
        );
    }
}
