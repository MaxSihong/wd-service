<?php

namespace Maxsihong\WdService\tests;

use Maxsihong\WdService\Entrance;
use PHPUnit\Framework\TestCase;

class WdCustomerTest extends TestCase
{
    protected Entrance $app;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->initApp();
    }

    protected function initApp()
    {
        $this->app = new Entrance([
            'app_id' => '1xxxxxx',
            'app_secret' => 'axxxxxx',
            'domain' => 'https://api.vdian.com/api',
            'redirect_uri' => 'https://xxx.com/callback',
            // redis
            'cache' => [
                "host" => "127.0.0.1",
                "port" => 6379,
                "database" => 0,
                "password" => "",
                'prefix' => 'wdcache_',
            ],
        ], ['uid' => 1, 'openid' => '1xxxxxx']);
    }

    public function testQueryCustomerInfo()
    {
        $data = $this->app::wdCustomer()->queryCustomerInfo('1xxx');
        var_dump($data);

        $this->assertIsArray($data);
    }

//    public function testQueryBuyerInfo()
//    {
//        $data = $this->app::wdCustomer()->queryBuyerInfo(['telephone' => 152xxx]);
//
//        $this->assertIsArray($data);
//    }
}
