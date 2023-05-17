<?php

declare(strict_types=1);

namespace Maxsihong\WdService\Commands;

use Maxsihong\WdService\Entrance;
use Maxsihong\WdService\Enums\WdOpenEnum;

/**
 * 微店 -- 每天主动刷新AccessToken
 */
class WdRefreshAccessToken
{

    /**
     * @var \Maxsihong\WdService\Entrance $app
     */
    protected $app;

    public function __construct()
    {
        $this->app = new Entrance([
            'app_id' => '1xxxxxx',
            'app_secret' => 'axxxxxx',
            'domain' => 'https://api.vdian.com/api',
            'redirect_uri' => 'https://xxx.com/callback', // 注意 xxx.com 为你服务商授权的推送域名，微店有白名单限制；后面的 callback 可自定义,改地址是回调接收微店返回的code和state，后续进行调用获取token操作
            // redis
            'cache' => [
                "host" => "127.0.0.1",
                "port" => 6379,
                "db" => 0,
                "auth" => "",
                "pconnect" => 1,
                'prefix' => 'wdcache_',
            ],
        ], ['uid' => 1, 'openid' => '1xxxxx']);
    }

    public function handle()
    {
        $expire_time = time() + 86460; // 一天过1分钟内会过期的token
        $need_refresh_tokens = $this->app::wdAuth()->getReadyExpireAccessTokens(0, $expire_time);
        if (empty($need_refresh_tokens)) {
            echo '暂无需要刷新的token';
            return true;
        }

        $access_token_expire_cache_key = $this->app::wdCommon()
            ->getBaseCacheKeyByType(WdOpenEnum::CACHE_KEY_ACCESS_TOKEN_EXPIRE);
        foreach ($need_refresh_tokens as $item) {
            $ref_status = true;
            list($uid, $openid) = explode('_', $item);

            try {
                // 主动刷新token
                $ref_status = $this->app::wdAuth()->initiativeRefreshToken($uid, $openid);
            } catch (\Exception $e) {
                // 记录错误日志
            }
            // 如果刷新失败则表示 token 超过30天，必须要用户重新授权
            if (!$ref_status) {
                $this->app::wdAuth()
                    ->setCacheConfig($access_token_expire_cache_key)
                    ->zrem($item);
            }
        }

        return true;
    }
}
