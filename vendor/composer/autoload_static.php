<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit21906f7122be690f3e77e71c928ab5a7
{
    public static $files = array (
        '8cd2fca4db21bffce1ad0612f7caeec4' => __DIR__ . '/..' . '/ramsey/array_column/src/array_column.php',
        '3937806105cc8e221b8fa8db5b70d2f2' => __DIR__ . '/..' . '/wp-cli/mustangostang-spyc/includes/functions.php',
        'be01b9b16925dcb22165c40b46681ac6' => __DIR__ . '/..' . '/wp-cli/php-cli-tools/lib/cli/cli.php',
    );

    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Symfony\\Component\\Finder\\' => 25,
        ),
        'M' => 
        array (
            'Mustangostang\\' => 14,
        ),
        'C' => 
        array (
            'Composer\\Installers\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Symfony\\Component\\Finder\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/finder',
        ),
        'Mustangostang\\' => 
        array (
            0 => __DIR__ . '/..' . '/wp-cli/mustangostang-spyc/src',
        ),
        'Composer\\Installers\\' => 
        array (
            0 => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers',
        ),
    );

    public static $prefixesPsr0 = array (
        'c' => 
        array (
            'cli' => 
            array (
                0 => __DIR__ . '/..' . '/wp-cli/php-cli-tools/lib',
            ),
        ),
        'W' => 
        array (
            'WP_CLI' => 
            array (
                0 => __DIR__ . '/..' . '/wp-cli/wp-cli/php',
            ),
        ),
        'R' => 
        array (
            'Requests' => 
            array (
                0 => __DIR__ . '/..' . '/rmccue/requests/library',
            ),
        ),
        'M' => 
        array (
            'Mustache' => 
            array (
                0 => __DIR__ . '/..' . '/mustache/mustache/src',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit21906f7122be690f3e77e71c928ab5a7::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit21906f7122be690f3e77e71c928ab5a7::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit21906f7122be690f3e77e71c928ab5a7::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}
