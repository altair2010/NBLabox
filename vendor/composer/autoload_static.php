<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit77743f46813e05eebb516660093cffc8
{
    public static $prefixLengthsPsr4 = array (
        'N' => 
        array (
            'NBLabox\\' => 8,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'NBLabox\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
            1 => __DIR__ . '/../..' . '/tests/unit',
        ),
    );

    public static $classMap = array (
        'NBLabox\\Box\\CurlSessionStub' => __DIR__ . '/../..' . '/tests/unit/Box/CurlSessionStub.php',
        'NBLabox\\Box\\NumericableBox' => __DIR__ . '/../..' . '/src/Box/NumericableBox.php',
        'NBLabox\\Box\\NumericableBoxTest' => __DIR__ . '/../..' . '/tests/unit/Box/NumericableBoxTest.php',
        'NBLabox\\Curl\\CurlRequest' => __DIR__ . '/../..' . '/src/Curl/CurlRequest.php',
        'NBLabox\\Curl\\CurlRequestTest' => __DIR__ . '/../..' . '/tests/unit/Curl/CurlRequestTest.php',
        'NBLabox\\Curl\\CurlResponse' => __DIR__ . '/../..' . '/src/Curl/CurlResponse.php',
        'NBLabox\\Curl\\CurlSession' => __DIR__ . '/../..' . '/src/Curl/CurlSession.php',
        'NBLabox\\Curl\\CurlSessionInterface' => __DIR__ . '/../..' . '/src/Curl/CurlSessionInterface.php',
        'NBLabox\\Curl\\CurlSessionTest' => __DIR__ . '/../..' . '/tests/unit/Curl/CurlSessionTest.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit77743f46813e05eebb516660093cffc8::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit77743f46813e05eebb516660093cffc8::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit77743f46813e05eebb516660093cffc8::$classMap;

        }, null, ClassLoader::class);
    }
}