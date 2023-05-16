<?php

declare(strict_types=1);

namespace Maxsihong\WdService\Kernel\Command;

use Maxsihong\WdService\Kernel\Container;
use Maxsihong\WdService\Kernel\Exception\ApiException;

class BaseClient
{
    /**
     * 驱动
     * @var string
     */
    private string $driver = '';

    protected string $cache_key = 'MAXSIHONG_WD_SERVICE';
    protected string $base_cache_key = '';

    private array $config = [];

    private ?Container $provider = null;

    /**
     * @var array|mixed 额外配置信息
     */
    private array $extend = [];

    protected string $app_id = '';

    protected string $app_secret = '';

    protected string $domain = '';

    protected int $timeout = 20;

    public function __construct(Container $provider)
    {
        $this->setProvider($provider);

        $this->setConfig($provider->offsetGet('open'));
        $this->setExtend($provider->offsetGet('extend'));
        $this->setDriver($provider->offsetGet('driver'));

        $this->app_id = $this->config['app_id'] ?? '';
        $this->app_secret = $this->config['app_secret'] ?? '';
        $this->domain = $this->config['domain'] ?? '';
        $this->timeout = $this->config['timeout'] ?? $this->timeout;
        if (empty($this->app_id) || empty($this->app_secret) || empty($this->domain)) {
            throw new ApiException('请配置好 [config] 内的appid、app_secret、domain');
        }

        // 初始化缓存key前缀
        $this->cache_key .= ':';
        $this->base_cache_key = $this->cache_key;
    }

    /**
     * 当前驱动
     */
    private function setDriver(string $driver): void
    {
        $this->driver = $driver;
    }

    /**
     * 当前驱动
     * @return string
     */
    protected function getDriver(): string
    {
        return $this->driver;
    }

    /**
     * @return string
     */
    protected function getCacheKey(): string
    {
        return $this->cache_key;
    }

    /**
     * @return string
     */
    public function getBaseCacheKey(): string
    {
        return $this->base_cache_key;
    }

    /**
     * @return array
     */
    protected function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @param array $config
     */
    private function setConfig(array $config): void
    {
        $this->config = $config;
    }

    /**
     * @return Container|null
     */
    protected function getProvider(): ?Container
    {
        return $this->provider;
    }

    /**
     * @param Container|null $provider
     */
    private function setProvider(?Container $provider): void
    {
        $this->provider = $provider;
    }

    /**
     * @return array
     */
    protected function getExtend(): array
    {
        return $this->extend;
    }

    /**
     * @param array $extend
     */
    private function setExtend(array $extend): void
    {
        $this->extend = $extend;
    }
}
