<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit38f1188a5c11b9a2026a52e7aebaf196
{
    public static $files = array (
        '5255c38a0faeba867671b61dfda6d864' => __DIR__ . '/..' . '/paragonie/random_compat/lib/random.php',
    );

    public static $prefixLengthsPsr4 = array (
        'c' => 
        array (
            'cebe\\markdown\\latex\\' => 20,
            'cebe\\markdown\\' => 14,
        ),
        'S' => 
        array (
            'Skautis\\' => 8,
        ),
        'R' => 
        array (
            'Ramsey\\Uuid\\' => 12,
        ),
        'P' => 
        array (
            'Psr\\Log\\' => 8,
        ),
        'M' => 
        array (
            'Mpdf\\' => 5,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'cebe\\markdown\\latex\\' => 
        array (
            0 => __DIR__ . '/..' . '/cebe/markdown-latex',
        ),
        'cebe\\markdown\\' => 
        array (
            0 => __DIR__ . '/..' . '/cebe/markdown',
        ),
        'Skautis\\' => 
        array (
            0 => __DIR__ . '/..' . '/skautis/skautis/src',
        ),
        'Ramsey\\Uuid\\' => 
        array (
            0 => __DIR__ . '/..' . '/ramsey/uuid/src',
        ),
        'Psr\\Log\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/log/Psr/Log',
        ),
        'Mpdf\\' => 
        array (
            0 => __DIR__ . '/..' . '/mpdf/mpdf/src',
        ),
    );

    public static $prefixesPsr0 = array (
        'M' => 
        array (
            'MikeVanRiel' => 
            array (
                0 => __DIR__ . '/..' . '/mikevanriel/text-to-latex/src',
                1 => __DIR__ . '/..' . '/mikevanriel/text-to-latex/tests/unit',
            ),
        ),
        'B' => 
        array (
            'BaconQrCode' => 
            array (
                0 => __DIR__ . '/..' . '/bacon/bacon-qr-code/src',
            ),
        ),
    );

    public static $classMap = array (
        'FPDF_TPL' => __DIR__ . '/..' . '/setasign/fpdi/fpdf_tpl.php',
        'FPDI' => __DIR__ . '/..' . '/setasign/fpdi/fpdi.php',
        'FilterASCII85' => __DIR__ . '/..' . '/setasign/fpdi/filters/FilterASCII85.php',
        'FilterASCIIHexDecode' => __DIR__ . '/..' . '/setasign/fpdi/filters/FilterASCIIHexDecode.php',
        'FilterLZW' => __DIR__ . '/..' . '/setasign/fpdi/filters/FilterLZW.php',
        'fpdi_pdf_parser' => __DIR__ . '/..' . '/setasign/fpdi/fpdi_pdf_parser.php',
        'pdf_context' => __DIR__ . '/..' . '/setasign/fpdi/pdf_context.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit38f1188a5c11b9a2026a52e7aebaf196::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit38f1188a5c11b9a2026a52e7aebaf196::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit38f1188a5c11b9a2026a52e7aebaf196::$prefixesPsr0;
            $loader->classMap = ComposerStaticInit38f1188a5c11b9a2026a52e7aebaf196::$classMap;

        }, null, ClassLoader::class);
    }
}
