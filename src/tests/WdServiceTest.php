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
            'domain' => 'hhh'
        ]);
    }

    public function testCallOrderTest()
    {
        $this->assertEquals($this->app::wdOrder()->test(), 'This is WdOrder.');
    }
}
