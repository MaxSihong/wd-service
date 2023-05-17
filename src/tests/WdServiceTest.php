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
            'app_id' => '1xxxxxx',
            'app_secret' => 'axxxxxx',
            'domain' => 'https://api.vdian.com/api',
            'redirect_uri' => 'https://xxx.com/callback', // 注意 xxx.com 为你服务商授权的推送域名，微店有白名单限制；后面的 callback 可自定义,改地址是回调接收微店返回的code和state，后续进行调用获取token操作
            // redis
            'cache' => [
                "host" => "127.0.0.1",
                "port" => 6379,
                "database" => 0,
                "password" => "",
                'prefix' => 'wdcache_',
            ],
        ], ['uid' => 1, 'openid' => '1xxxxxx']);
        /**
         * 注意 后面的参数可不传是不影响创建容器的，默认是uid-0；但后面记得需要初始化这两个值(`reSetUidAndOpenidAndCacheKey`)，因为缓存都是基于这两个值存储的
         * 可以理解为uid是用户，而openid则是店铺，一个用户有多个店铺，这样的关系；
         * 所以后续存储缓存，包括access_token都是基于uid和openid为base_key做缓存的
         */
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

//    public function testAuthGetCode()
//    {
//        $url = $this->app::wdAuth()->getCodeByOauth(0, false);
//        var_dump($url);
//
//        $this->assertIsString($url);
//    }

//    public function testAuthGetToken()
//    {
//        $access_token = $this->app::wdAuth()->getAccessTokenByRedirectUriCode('ae49574061a46829a3e84930214eca04');
//        var_dump($access_token);
//
//        $this->assertIsArray($access_token);
//    }

    public function testAuthGetAccessToken()
    {
        $access_token = $this->app::wdAuth()->getAccessToken();
        echo "\n";
        var_dump($access_token);
        echo "\n";

        $this->assertIsString($access_token);
    }

//    public function testAuthRefreshToken()
//    {
//        $access_token = $this->app::wdAuth()->refreshToken();
//        echo "\n";
//        var_dump($access_token);
//        echo "\n";
//
//        $this->assertIsArray($access_token);
//    }

//    public function testCommonUpload()
//    {
//        $url = $this->app::wdCommon()->uploadFile('https://lmg.jj20.com/up/allimg/tp09/210H51R3313N3-0-lp.jpg');
//
//        $this->assertIsString($url);
//    }

    public function testCommonEncrypt()
    {
        $str = $this->app::wdCommon()->encrypt('123456789');
        echo "\n 加密：{$str} \n";

        $this->assertIsString($str);
    }

    public function testCommonDecrypt()
    {
        $str = $this->app::wdCommon()->decrypt('aL5TyQc/s9ibaFkneMXr/nLuBklWuJ/NCqF+pZSJWUc=');
        echo "\n 解密：{$str} \n";

        $this->assertIsString($str);
    }
}
