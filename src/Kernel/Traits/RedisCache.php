<?php

declare(strict_types=1);

namespace Maxsihong\WdService\Kernel\Traits;

trait RedisCache
{
    /**
     * 缓存key
     * @var string
     */
    protected string $key = '';

    /**
     * 过期时间
     * @var int|null
     */
    protected ?int $expire = null;

    /**
     * hash field
     * @var string
     */
    protected string $hash_field = '';

    protected \Maxsihong\WdService\Kernel\Support\Redis $redis;

    /**
     * 设置缓存初始信息
     * @param string $key
     * @param int|null $expire
     * @param string $field
     * @return $this
     * @author: 陈志洪
     * @since: 2023/5/16
     */
    public function setCacheConfig(string $key, ?int $expire = null, string $field = '')
    {
        $this->key = $key;
        $this->expire = $expire;
        $this->hash_field = $field;

        return $this;
    }

    /**
     * 设置缓存
     * @param $value
     * @return bool|\Redis
     * @throws \RedisException
     * @since: 2023/5/16
     * @author: 陈志洪
     */
    public function set($value)
    {
        // 序列化
        // $value = is_scalar($value) ? $value : 'think_serialize:' . serialize($value);

        if ($this->expire) {
            return $this->redis->setex($this->key, $this->expire, $value);
        } else {
            return $this->redis->set($this->key, $value, $this->expire);
        }
    }

    /**
     * 获取缓存
     * @return false|mixed|\Redis|string
     * @throws \RedisException
     * @author: 陈志洪
     * @since: 2023/5/16
     */
    public function get()
    {
        return $this->redis->get($this->key);
    }
}