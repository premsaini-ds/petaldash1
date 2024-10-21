<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInit053c665e1b5dd0592182624a2b88e5da
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        spl_autoload_register(array('ComposerAutoloaderInit053c665e1b5dd0592182624a2b88e5da', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInit053c665e1b5dd0592182624a2b88e5da', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        call_user_func(\Composer\Autoload\ComposerStaticInit053c665e1b5dd0592182624a2b88e5da::getInitializer($loader));

        $loader->register(true);

        return $loader;
    }
}