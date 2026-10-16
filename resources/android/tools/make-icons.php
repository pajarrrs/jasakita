<?php

/**
 * Generator ikon launcher Android untuk aplikasi JasaKita.
 *
 * Pemakaian: php make-icons.php <folder-res> <label> <warna-hex>
 * Contoh:    php make-icons.php build/res "JK" "#0D9488"
 */
$resourceDir = $argv[1] ?? null;
$label = $argv[2] ?? 'JK';
$hex = ltrim($argv[3] ?? '#0D9488', '#');

if ($resourceDir === null || ! is_dir($resourceDir)) {
    fwrite(STDERR, "Folder resource tidak ditemukan.\n");
    exit(1);
}

$sizes = [
    'mdpi' => 48,
    'hdpi' => 72,
    'xhdpi' => 96,
    'xxhdpi' => 144,
    'xxxhdpi' => 192,
];

$fonts = [
    '/System/Library/Fonts/Supplemental/Arial Bold.ttf',
    '/Library/Fonts/Arial Bold.ttf',
    '/System/Library/Fonts/Helvetica.ttc',
];

$font = null;

foreach ($fonts as $candidate) {
    if (is_file($candidate)) {
        $font = $candidate;
        break;
    }
}

[$red, $green, $blue] = array_map('hexdec', str_split($hex, 2));

foreach ($sizes as $density => $size) {
    $directory = $resourceDir.'/mipmap-'.$density;

    if (! is_dir($directory) && ! mkdir($directory, 0777, true) && ! is_dir($directory)) {
        fwrite(STDERR, "Tidak bisa membuat folder {$directory}.\n");
        exit(1);
    }

    $image = imagecreatetruecolor($size, $size);
    imagesavealpha($image, true);

    $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
    imagefill($image, 0, 0, $transparent);

    $background = imagecolorallocate($image, $red, $green, $blue);
    $radius = (int) round($size * 0.24);

    imagefilledrectangle($image, $radius, 0, $size - $radius - 1, $size - 1, $background);
    imagefilledrectangle($image, 0, $radius, $size - 1, $size - $radius - 1, $background);

    foreach ([[$radius, $radius], [$size - $radius - 1, $radius], [$radius, $size - $radius - 1], [$size - $radius - 1, $size - $radius - 1]] as [$cx, $cy]) {
        imagefilledellipse($image, $cx, $cy, $radius * 2, $radius * 2, $background);
    }

    if ($font !== null) {
        $white = imagecolorallocate($image, 255, 255, 255);
        $fontSize = $size * 0.42;
        $box = imagettfbbox($fontSize, 0, $font, $label);
        $textWidth = $box[2] - $box[0];
        $textHeight = $box[1] - $box[7];
        $x = (int) round(($size - $textWidth) / 2) - $box[0];
        $y = (int) round(($size + $textHeight) / 2) - $box[1];

        imagettftext($image, $fontSize, 0, $x, $y, $white, $font, $label);
    }

    imagepng($image, $directory.'/ic_launcher.png');
    imagedestroy($image);
}

echo "Ikon dibuat untuk label {$label}.\n";
