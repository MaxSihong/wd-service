<?php

namespace Maxsihong\WdService\tests;

use Maxsihong\WdService\Entrance;
use PHPUnit\Framework\TestCase;

class WdServiceTest extends TestCase
{
    protected Entrance $app;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->app = new Entrance([
            'app_id' => '1',
            'app_secret' => '1',
            'domain' => 'hhh',
            // redis
            'cache' => [
                "host" => "127.0.0.1",
                "port" => 6379,
                "db" => 0,
                "auth" => "",
                "pconnect" => 1,
                'prefix' => 'wdcache_',
            ],
        ]);
    }

    public function testCallOrderTest()
    {
        // check result is 'This is WdTest.'
        $this->assertEquals($this->app::wdTest()->test(), 'This is WdTest.');
    }

    public function testSetAndGetRedis()
    {
        $this->assertEquals($this->app::wdTest()->testRedis(), 'redis success!');
    }
}
