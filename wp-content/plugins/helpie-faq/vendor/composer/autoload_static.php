<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit053c665e1b5dd0592182624a2b88e5da
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'Pauple\\Pluginator\\' => 18,
        ),
        'C' => 
        array (
            'Composer\\Installers\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Pauple\\Pluginator\\' => 
        array (
            0 => __DIR__ . '/..' . '/pauple/pluginator/src',
        ),
        'Composer\\Installers\\' => 
        array (
            0 => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit053c665e1b5dd0592182624a2b88e5da::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit053c665e1b5dd0592182624a2b88e5da::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit053c665e1b5dd0592182624a2b88e5da::$classMap;

        }, null, ClassLoader::class);
    }
}
