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
         $value = is_scalar($value) ? $value : 'think_serialize:' . serialize($value);

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
        $info = $this->redis->get($this->key);

        if ($info && strpos($info, 'think_serialize:') !== false) {
            $info = unserialize(substr($info, 16));
        }

        return $info;
    }

    /**
     * 添加有序集合
     * @param int $score 成员的分数
     * @param string $member 成员的名称
     * @return mixed
     * @since: 2023/5/17
     * @author: 陈志洪
     */
    public function zadd(int $score, string $member)
    {
        return $this->redis->zadd($this->key, $score, $member);
    }

    /**
     * 移除指定元素
     * @param string $member 成员的名称
     * @return mixed
     * @author: 陈志洪
     * @since: 2023/5/17
     */
    public function zrem(string $member)
    {
        return $this->redis->zrem($this->key, $member);
    }

    /**
     * 按顺序/降序返回表中指定索引区间的元素
     * @param int $score1
     * @param int $score2
     * @param array $extend_data
     * @return mixed
     * @author: 陈志洪
     * @since: 2023/5/17
     */
    public function zrangebyscore(int $score1, int $score2, array $extend_data = [])
    {
        return $this->redis->zrangebyscore($this->key, $score1, $score2, $extend_data);
    }
}