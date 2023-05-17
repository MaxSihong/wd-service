<?php

declare(strict_types=1);

namespace Maxsihong\WdService\Kernel\Providers;

use Maxsihong\WdService\Kernel\Container;
use Maxsihong\WdService\Kernel\Contracts\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    /**
     * 根据当前驱动初始化类到容器
     */
    public function register(Container $pimple)
    {
        // 反射src目录下所有Wd开头的文件夹，将所有类都注入到容器
        $folders = glob(dirname(__DIR__, 2) . '/Wd*');

        foreach ($folders as $folder) {
            $model = basename($folder);
            $files = glob($folder . "/*.php");

            $object = new \ArrayObject();
            foreach ($files as $file) {
                $class_name = basename($file, '.php'); // 截取获取到类名称

                $init_class_name = "\\Maxsihong\\WdService\\{$model}\\{$class_name}";
                $pimple[$class_name] = function ($pimple) use ($init_class_name) {
                    return new $init_class_name($pimple);
                };
            }
        }

    }
}
