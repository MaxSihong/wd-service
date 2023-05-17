<?php

namespace Maxsihong\WdService\WdService;

use Maxsihong\WdService\Kernel\HttpClient\Client;

class WdTest extends Client
{
    public function test()
    {
        return 'This is WdTest.';
    }

    public function testRedis()
    {
        $this->setCacheConfig($this->cache_key . 'test_redis')
            ->set('redis success!');

        return $this->setCacheConfig($this->cache_key . 'test_redis')
                ->get();
    }
}
