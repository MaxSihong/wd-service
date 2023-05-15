<?php

declare(strict_types=1);

namespace Maxsihong\WdService;

class Factory
{
    public static function make(string $name, array $config)
    {
        $namespace = strToGreatHump($name);
        $application = "\\Maxsihong\\WdService\\{$namespace}\\Application";

        return new $application($config);
    }

    public static function __callStatic(string $name, array $arguments)
    {
        return self::make($name, ...$arguments);
    }
}
