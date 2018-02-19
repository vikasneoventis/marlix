<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit166d643fc4e3776a95bbeab88e55d11d
{
    public static $prefixLengthsPsr4 = array (
        'T' => 
        array (
            'Trollweb\\BringApi\\' => 18,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Trollweb\\BringApi\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit166d643fc4e3776a95bbeab88e55d11d::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit166d643fc4e3776a95bbeab88e55d11d::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
