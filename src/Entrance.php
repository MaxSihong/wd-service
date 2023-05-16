<?php

namespace Maxsihong\WdService;

use Maxsihong\WdService\Kernel\Application;

class Entrance
{
    protected static $instance = null;

    protected static array $config = [];

    protected static array $init_param = [];

    public function __construct(array $config, array $init_param = [])
    {
        self::$config = $config;
        self::$init_param = $init_param;
    }

    /**
     * 初始化
     * @return Application
     */
    public static function application(): Application
    {
        self::$instance === null && (self::$instance = new Application(self::$config, self::$init_param));
        return self::$instance;
    }

    public static function wdOrder()
    {
        return self::application()->WdOrder;
    }
}
