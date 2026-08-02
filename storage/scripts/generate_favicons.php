<?php

// Generate favicons for Riof (brand: R monogram on indigo->blue gradient)
$font = 'C:/Windows/Fonts/arialbd.ttf';

function hexToRgb(string $hex): array
{
    $hex = ltrim($hex, '#');
    return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
}

function lerp(float $t, array $a, array $b): array
{
    return [
        (int) round($a[0] + ($b[0] - $a[0]) * $t),
        (int) round($a[1] + ($b[1] - $a[1]) * $t),
        (int) round($a[2] + ($b[2] - $a[2]) * $t),
    ];
}

function buildIcon(int $size, string $font): \GdImage
{
    $scale = 4; // supersample factor for smooth edges
    $big = buildIconRaw($size * $scale, $font);

    $img = imagecreatetruecolor($size, $size);
    imagesavealpha($img, true);
    $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
    imagefill($img, 0, 0, $transparent);
    imagealphablending($img, true);
    imagecopyresampled($img, $big, 0, 0, 0, 0, $size, $size, $size * $scale, $size * $scale);
    return $img;
}

function buildIconRaw(int $size, string $font): \GdImage
{
    $img = imagecreatetruecolor($size, $size);
    imagesavealpha($img, true);
    $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
    imagefill($img, 0, 0, $transparent);

    $c1 = hexToRgb('6366f1'); // accent
    $c2 = hexToRgb('3b82f6'); // accent-light
    $radius = (int) round($size * 0.24);
    $corner = $size - $radius - 1; // corner circle center coordinate

    // Paint the diagonal gradient directly inside the rounded-rect shape
    for ($y = 0; $y < $size; $y++) {
        for ($x = 0; $x < $size; $x++) {
            $inside = false;
            if ($x >= $radius && $x < $size - $radius) {
                $inside = true;                 // vertical middle band
            } elseif ($y >= $radius && $y < $size - $radius) {
                $inside = true;                 // horizontal middle band
            } else {
                $cx = $x < $radius ? $radius : $corner;
                $cy = $y < $radius ? $radius : $corner;
                $dx = $x - $cx;
                $dy = $y - $cy;
                $inside = ($dx * $dx + $dy * $dy) <= ($radius * $radius); // corner quarter-circle
            }

            if ($inside) {
                $t = ($x + $y) / (2 * ($size - 1));
                $rgb = lerp($t, $c1, $c2);
                $col = imagecolorallocate($img, $rgb[0], $rgb[1], $rgb[2]);
                imagesetpixel($img, $x, $y, $col);
            }
        }
    }

    // letter R
    $fontSize = $size * 0.56;
    $bbox = imagettfbbox($fontSize, 0, $font, 'R');
    $textW = $bbox[2] - $bbox[0];
    $textH = $bbox[1] - $bbox[7];
    $x = (int) round(($size - $textW) / 2 - $bbox[0]);
    $y = (int) round(($size - $textH) / 2 - $bbox[7] + $size * 0.03);

    $whiteCol = imagecolorallocate($img, 255, 255, 255);
    imagettftext($img, $fontSize, 0, $x, $y, $whiteCol, $font, 'R');

    // small accent dot (bottom-right)
    $dotR = max(2, (int) round($size * 0.05));
    $dx = $size - (int) round($size * 0.26);
    $dy = (int) round($size * 0.74);
    $dot = imagecolorallocatealpha($img, 255, 255, 255, 20);
    imagefilledellipse($img, $dx, $dy, $dotR * 2, $dotR * 2, $dot);

    return $img;
}

$out = dirname(__DIR__, 2) . '/public';

// PNGs
imagepng(buildIcon(180, $font), "$out/apple-touch-icon.png");
imagepng(buildIcon(32, $font), "$out/favicon-32x32.png");
imagepng(buildIcon(16, $font), "$out/favicon-16x16.png");
imagepng(buildIcon(512, $font), "$out/android-chrome-512x512.png");
imagepng(buildIcon(192, $font), "$out/android-chrome-192x192.png");

// Build multi-size .ico (PNG-encoded entries, supported by modern browsers/Windows)
function pngData(GdImage $img, int $size): string
{
    ob_start();
    imagepng($img);
    return ob_get_clean();
}

function buildIco(string $font, string $outPath): void
{
    $sizes = [16, 32, 48];
    $images = [];
    foreach ($sizes as $s) {
        $images[$s] = pngData(buildIcon($s, $font), $s);
    }

    $ico = '';
    $ico .= pack('v', 0);            // reserved
    $ico .= pack('v', 1);            // type: icon
    $ico .= pack('v', count($images)); // count

    $offset = 6 + 16 * count($images);
    foreach ($sizes as $s) {
        $data = $images[$s];
        $bw = ($s >= 256) ? 0 : $s;
        $ico .= pack('C', $bw);                      // width
        $ico .= pack('C', $bw);                      // height
        $ico .= pack('C', 0);                        // palette
        $ico .= pack('C', 0);                        // reserved
        $ico .= pack('v', 1);                        // color planes
        $ico .= pack('v', 32);                       // bpp
        $ico .= pack('V', strlen($data));            // data size
        $ico .= pack('V', $offset);                  // data offset
        $offset += strlen($data);
    }
    foreach ($images as $data) {
        $ico .= $data;
    }

    file_put_contents($outPath, $ico);
}

buildIco($font, "$out/favicon.ico");

echo "Done: apple-touch-icon.png, favicon-32x32.png, favicon-16x16.png, favicon.ico, android-chrome icons\n";
