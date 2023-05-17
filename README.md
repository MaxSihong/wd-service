<h1 align="left"><a href="https://github.com/MaxSihong/wd-service">微店服务商扩展</a></h1>

📦 微店服务商扩展是一个基于PHP的扩展，用于在微店平台上开发服务商应用。

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
<img alt="GitHub code size in bytes" src="https://img.shields.io/github/languages/code-size/MaxSihong/wd-service">

## 环境需求

- PHP >= 7.3.0

## 安装

```bash
composer require 
```

## 使用示例

实例化容器:
```php
// 配置
$config = [
    'app_id' => '1xxxxxx', // 服务商appid
    'app_secret' => 'axxxxxx', // 服务商secret
    'domain' => 'https://api.vdian.com/api', // 微店api地址
    'redirect_uri' => 'https://xxx.com/callback', // 注意 xxx.com 为你服务商授权的推送域名，微店有白名单限制；后面的 callback 可自定义,改地址是回调接收微店返回的code和state，后续进行调用获取token操作
    // redis
    'cache' => [
        "host" => "127.0.0.1",
        "port" => 6379,
        "database" => 0, // 选择redis库,0~15 共16个库
        "password" => "", // 密码
        'prefix' => 'wdcache_', // 前缀
    ],
];
// 相关用户和店铺（可不传），但后面记得需要初始化这两个值
$init_param = ['uid' => 1, 'openid' => '1xxxx'];

$app = new \Maxsihong\WdService\Entrance($config, $init_param);

/**
 * 注意 后面的参数可不传是不影响创建容器的，默认是uid-0；但后面记得需要初始化这两个值(`reSetUidAndOpenidAndCacheKey`)，因为缓存都是基于这两个值存储的
 * 可以理解为uid是用户，而openid则是店铺，一个用户有多个店铺，这样的关系；
 * 所以后续存储缓存，包括access_token都是基于uid和openid为base_key做缓存的
 */
```

使用
```php
// 加密数据
$str = $app::wdCommon()->encrypt('123456789');
```

Laravel内实例化容器，可以放到服务内
```php
// 在 `App\Providers\AppServiceProvider` 类
public function register()
{
    // 注册 微店 服务容器
    $this->app->bind("onlineretailers.wd", function ($app, $init_param) {
        // 微店的话必须初始化经销商uid
        if (empty($init_param) || !isset($init_param['uid'])) {
            throw new ApiException('必须要初始化用户uid');
        }

        $init_param['openid'] = $init_param['openid'] ?? '';

        // $config 可放如config内，使用config('xx')获取
        return new \Maxsihong\WdService\Entrance($config, $init_param);
    });
}
```

Laravel容器方式使用
```php
// 获取容器
$app = app('onlineretailers.wd', ['uid' => 1, 'openid' => '1xxxx']);
// 加密数据
$str = $app::wdCommon()->encrypt('123456789');
```

## License

MIT