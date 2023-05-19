<h1><a href="https://github.com/MaxSihong/wd-service">微店服务商扩展</a></h1>

📦 微店服务商扩展是一个基于PHP的扩展，用于在微店平台上开发服务商应用。

<img alt="GitHub code size in bytes" src="https://img.shields.io/github/languages/code-size/MaxSihong/wd-service"></img>
[![Latest Stable Version](https://poser.pugx.org/MaxSihong/wd-service/v/stable.svg)](https://packagist.org/packages/MaxSihong/wd-servicet)
[![Latest Unstable Version](https://poser.pugx.org/MaxSihong/wd-service/v/unstable.svg)](https://packagist.org/packages/MaxSihong/wd-service)
[![Total Downloads](https://poser.pugx.org/MaxSihong/wd-service/downloads)](https://packagist.org/packages/MaxSihong/wd-service)
[![License](https://poser.pugx.org/MaxSihong/wd-service/license)](https://packagist.org/packages/MaxSihong/wd-service)

## 进度

> 目前只打算把订单、商品模块整体封装，剩余模块后续看情况添加吧。如果需要其他模块使用可以自行 Fork 并提交合并，或者联系我协助添加。

- [x] 项目搭建、容器封装、基础接口封装
- [ ] 【订单管理】接口封装 (46%)
- [ ] 【商品管理】接口封装 (32%)

## 环境需求

- PHP >= 7.3.0

## 安装

```bash
composer require maxsihong/wd-service
```

## 使用示例

实例化:
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

## Laravel 使用示例

注册服务
```php
// 在 `App\Providers\AppServiceProvider` 类
public function register()
{
    // 注册 微店 服务容器
    $this->app->bind("onlineretailers.wd", function ($app, $init_param) {
        /**
        * 初始化用户uid，这个判断可根据自己业务来判断是否需要
        * 不加的话则直接初始化即可：$init_param['uid'] = $init_param['uid'] ?? 0;
         */
        if (empty($init_param) || !isset($init_param['uid'])) {
            throw new \Maxsihong\WdService\Kernel\Exception\ApiException('必须要初始化用户uid');
        }

        $init_param['openid'] = $init_param['openid'] ?? '';

        // $config 可放如config内，使用config('xx')获取
        return new \Maxsihong\WdService\Entrance($config, $init_param);
    });
}
```

使用
```php
// 获取容器
// 想要idea更好的提示，可增加下面 @var 的注释
/** @var \Maxsihong\WdService\Entrance $app */
$app = app('onlineretailers.wd', ['uid' => 1, 'openid' => '1xxxx']);
// 加密数据
$str = $app::wdCommon()->encrypt('123456789');
```

## License

MIT