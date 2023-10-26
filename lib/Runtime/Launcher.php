<?php

/**
 * @author      Mohammed Moussaoui
 * @copyright   Copyright (c) Mohammed Moussaoui. All rights reserved.
 * @license     MIT License. For full license information see LICENSE file in the project root.
 * @link        https://github.com/DevNet-Framework
 */

namespace DevNet\System\Runtime;

use DevNet\System\Exceptions\ClassException;
use DevNet\System\Exceptions\MethodException;
use ReflectionMethod;

class Launcher extends LauncherProperties
{
    public function __construct(ClassLoader $loader)
    {
        static::$classLoader = $loader;
    }

    public function launch(array $args = [], ?string $mainClass = null): void
    {
        $root = scandir(static::$rootDirectory);

        foreach ($root as $dir) {
            if (!is_dir(static::$rootDirectory . '/' . $dir) || str_starts_with($dir, '.')) continue;
            static::$classLoader->map(static::$rootNamespace, '/' . $dir);
        }

        static::$classLoader->register();
        self::$arguments = $args;
        if ($mainClass) {
            static::$startupObject = $mainClass;
        }

        if (!class_exists(static::$startupObject)) {
            throw new ClassException("Could not find the entry point class: " . static::$startupObject, 0, 1);
        }

        if (!method_exists(static::$startupObject, 'main')) {
            throw new MethodException(static::$startupObject . " does not contain a static method 'main' to be suitable for an entry point!", 0, 1);
        }

        static::$entryPoint = new ReflectionMethod(static::$startupObject, 'main');
        static::$entryPoint->invoke(null, $args);
    }

    public static function initialize(string $projectPath): ?static
    {
        if (!file_exists($projectPath)) {
            return null;
        }

        $projectFile = simplexml_load_file($projectPath);
        if (!$projectFile) {
            return null;
        }

        static::$rootDirectory = $root = dirname($projectPath);
        static::$rootNamespace = $projectFile->Properties->RootNamespace ?? 'Application';
        static::$startupObject = $projectFile->Properties->StartupObject ?? 'Application\\Program';
        $codeFiles = $projectFile->Items->CodeFile ?? [];

        // load local packages including composer
        foreach ($codeFiles as $codeFile) {
            $file = (string)$codeFile->attributes()->include;
            if (is_file($root . '/' . $file)) {
                require $root . '/' . $file;
            }
        }

        return new static(new ClassLoader($root));
    }
}
