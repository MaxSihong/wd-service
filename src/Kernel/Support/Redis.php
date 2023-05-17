<?php

declare(strict_types=1);

namespace Maxsihong\WdService\Kernel\Support;

use Maxsihong\WdService\Kernel\Exception\ApiException;

class Redis
{
    const REDIS_TIMEOUT = 0; //超时
    private static array $_instance = []; //类单例数组
    private int $hash; // 选择redis库,0~15 共16个库
    private \Redis $redis; // redis连接句柄

    private function __construct($redis_config = [])
    {

        $this->redis = new \Redis();
        $this->hash = $redis_config["db"] ?? 0;

        if ($redis_config["pconnect"]) {
            // 长链接，host，端口，超过 x 秒放弃链接
            $this->redis->pconnect($redis_config['host'], $redis_config['port'], self::REDIS_TIMEOUT);
        } else {
            //短链接
            $this->redis->connect($redis_config['host'], $redis_config['port'], self::REDIS_TIMEOUT);
        }

        // 设置连接密码
        if ($redis_config["auth"]) {
            $this->redis->auth($redis_config["auth"]);
        }

        // 选择库 0-15
        $this->redis->select($this->hash);
    }

    //外部获取实例
    public static function getInstance($redis_config)
    {
        if (!isset(self::$_instance[$redis_config["db"]])) {
            self::$_instance[$redis_config["db"]] = new self($redis_config);
        }

        //防止挂掉
        try {
            self::$_instance[$redis_config["db"]]->Ping() == 'Pong';
        } catch (\Exception $e) {
            throw new ApiException('redis client error. msg: ' . $e->getMessage());
        }

        return self::$_instance[$redis_config["db"]];
    }

    //获取redis的连接实例
    public function getRedisConnect(): \Redis
    {
        return $this->redis;
    }

    public function __call($method, $args)
    {
        return call_user_func_array([$this->redis, $method], $args);
    }

    /**
     * 关闭单例时做清理工作
     */
    public function __destruct()
    {
        $key = $this->hash;
        $this->redis->close();
        self::$_instance[$key] = null;
    }

    private function __clone()
    {
    }
}
