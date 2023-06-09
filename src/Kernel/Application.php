<?php

declare(strict_types=1);

namespace Maxsihong\WdService\Kernel;

use Maxsihong\WdService\Kernel\Command\BaseApplication;
use Maxsihong\WdService\Kernel\Providers\ServiceProvider;

/**
 * 微店
 *
 * @property \Maxsihong\WdService\WdService\WdTest $WdTest 测试
 * @property \Maxsihong\WdService\WdService\WdAuth $WdAuth 授权
 * @property \Maxsihong\WdService\WdService\WdCommon $WdCommon 公用
 * @property \Maxsihong\WdService\WdService\WdCustomer $WdCustomer 用户
 * @property \Maxsihong\WdService\WdService\WdItem $WdItem 商品
 * @property \Maxsihong\WdService\WdService\WdOrder $WdOrder 订单
 */
class Application extends BaseApplication
{
    /**
     * @var array|string[]
     */
    protected array $providersNew = [
        ServiceProvider::class,
    ];

    public function __construct($config, $init_param = [])
    {
        $this->driver = 'wd';
        $this->providers = array_merge($this->providers, $this->providersNew);

        $config = array_merge(['open' => $config], ['extend' => $init_param, 'driver' => $this->driver]);

        parent::__construct($config);
    }
}
