<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitadc286071bc3a7fdce0f7a16865e3688
{
    public static $prefixLengthsPsr4 = array (
        'G' => 
        array (
            'GraphQL\\' => 8,
        ),
        'E' => 
        array (
            'Ergonode\\IntegrationShopware\\Tests\\' => 35,
            'Ergonode\\IntegrationShopware\\' => 29,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'GraphQL\\' => 
        array (
            0 => __DIR__ . '/..' . '/gmostafa/php-graphql-client/src',
        ),
        'Ergonode\\IntegrationShopware\\Tests\\' => 
        array (
            0 => __DIR__ . '/../..' . '/tests',
        ),
        'Ergonode\\IntegrationShopware\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitadc286071bc3a7fdce0f7a16865e3688::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitadc286071bc3a7fdce0f7a16865e3688::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitadc286071bc3a7fdce0f7a16865e3688::$classMap;

        }, null, ClassLoader::class);
    }
}
