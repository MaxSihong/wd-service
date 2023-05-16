<?php

declare(strict_types=1);

namespace Maxsihong\WdService\Kernel\Command;

use Maxsihong\WdService\Kernel\Container;

class BaseApplication extends Container
{
    /**
     * 驱动
     * @var string
     */
    protected string $driver = 'wd';

    protected array $config = [];

    /**
     * Service Providers.
     *
     * @var array
     */
    protected array $providers = [];

    public function __construct(array $values = [])
    {
        parent::__construct($values);

        $this->registerProviders();
    }

    public function registerProviders()
    {
        foreach ($this->providers as $provider) {
            $this->register(new $provider());
        }
    }

    public function __get($key)
    {
        return $this->offsetGet($key);
    }
}
