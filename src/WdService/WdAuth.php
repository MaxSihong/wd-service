<?php

declare(strict_types=1);

namespace Maxsihong\WdService\WdService;

use Maxsihong\WdService\Enums\WdOpenEnum;
use Maxsihong\WdService\Kernel\Exception\ApiException;
use Maxsihong\WdService\Kernel\HttpClient\Client;

class WdAuth extends Client
{
    /**
     * 获取 access_token
     * @param bool $only_token 是否只获取token，还是要包含商家的额外信息
     * @param bool $active_auth 是否主动重定向授权
     * @return false|mixed|\Redis|string|null
     * @throws \RedisException
     * @author: 陈志洪
     * @since: 2023/5/17
     */
    public function getAccessToken(bool $only_token = true, bool $active_auth = true)
    {
        $cache_key = $this->getCacheKeyByCacheType(WdOpenEnum::CACHE_KEY_ACCESS_TOKEN);

        $access_token_info = $this->setCacheConfig($cache_key)->get();
        // 没有则需要用户重新授权店铺信息
        if (empty($access_token_info)) {
            return $active_auth ? $this->getCodeByOauth() : false;
        }

        // 判断token过期时间，如果过期则去刷新token
        if ($access_token_info['expire_time'] <= time()) {
            $access_token_info = $this->refreshToken($access_token_info['refresh_token'], $access_token_info['refreshExpireTime']);
        }

        return $only_token ? $access_token_info['access_token'] : $access_token_info;
    }

    /**
     * 获取code
     * @link https://open.weidian.com/#/guide/145 【三、服务型应用获取Token】【3. 获取code】
     * @param int $uid 店铺id
     * @param bool $is_redirect 是否直接重定向去授权
     * @return string|void
     * @since: 2023/5/17
     * @author: 陈志洪
     */
    public function getCodeByOauth(int $uid = 0, bool $is_redirect = true)
    {
        /**
         * 如果用户同意授权，页面将跳转至 redirect_uri/?code=CODE&state=STATE。
         * 若用户禁止授权，则重定向后不会带上code参数，仅会带上state参数redirecturi?state=STATE code作为换取accesstoken的票据
         * 每次用户授权带上的code将不一样，code只能使用一次，5分钟未被使用自动过期
         */
        $param = http_build_query([
            'appkey' => $this->app_id, // 微店应用的唯一标识
            'redirect_uri' => $this->getConfig()['redirect_uri'], // 授权后重定向的回调链接地址，请使用urlencode对链接进行处理
            'response_type' => 'code',
            'state' => 'oauth' . ($uid ?: "{$this->getUid()}"), // 重定向后会带上state参数，开发者可以填写a-zA-Z0-9的参数值
        ]);

        // 与客户端协商返回的地址
        $redirect_url = "https://oauth.open.weidian.com/oauth2/authorize?{$param}";
        if (!$is_redirect) {
            return $redirect_url;
        }

        // 重定向用户授权店铺信息，最终回调内得到code，换取token和商家信息
        header("Location: {$redirect_url}");
        exit;
    }

    /**
     * 获取token
     * @link https://open.weidian.com/#/guide/145 【三、服务型应用获取Token】【4. 获取token】
     * @param string $code 回调code
     * @return array
     * @throws \RedisException
     * @since: 2023/5/17
     * @author: 陈志洪
     */
    public function getAccessTokenByRedirectUriCode(string $code): array
    {
        $param = http_build_query([
            'appkey' => $this->app_id, // 微店应用的唯一标识
            'secret' => $this->app_secret,
            'code' => $code,
            'grant_type' => 'authorization_code',
        ]);
        $result = request_url("https://oauth.open.weidian.com/oauth2/access_token?{$param}");

        $result = json_decode($result, true);
        if (!is_array($result)) {
            $result = json_decode($result, true);
        }

        // 校验响应数据是否异常
        wd_check_result_err($result, 'oauth2/access_token', []);

        $access_token_info = $result['result'];
        $access_token_info['expire_time'] = time() + ($access_token_info['expire_in'] - 60); // 设置token的过期时间 提前一分钟过期

        $this->reSetUidAndOpenidAndCacheKey(0, $access_token_info['openid']); // 重置openid，重新生成完整的缓存key

        // 重置 AccessToken redis的过期时间集合
        $this->resetExpireAccessTokenRedisList($access_token_info['expire_in'] - 60);

        $cache_key = $this->getCacheKeyByCacheType(WdOpenEnum::CACHE_KEY_ACCESS_TOKEN);

        // 缓存时间以刷新token的时间做缓存，(毫秒 转 秒 - 一分钟)
        $this->setCacheConfig($cache_key, intval($access_token_info['refreshExpireTime'] / 1000 - 60))
            ->set($access_token_info);

        return $access_token_info;
    }

    /**
     * 刷新token
     * @link https://open.weidian.com/#/guide/145 【三、服务型应用获取Token】【5. 刷新token】
     * @param string $refresh_token
     * @param int $refreshExpireTime
     * @return mixed
     * @throws \RedisException
     * @author: 陈志洪
     * @since: 2023/5/17
     */
    public function refreshToken(string $refresh_token = '', int $refreshExpireTime = 0)
    {
        // 如果是都没传，则表示是请求别的接口时导致的超时，则主动走一次验证token的流程在进行判断是要刷新还是重新授权
        if (empty($refresh_token) && empty($refreshExpireTime)) {
            $access_token_info = $this->getAccessToken(false);
            if (empty($access_token_info) || !isset($access_token_info['refresh_token']) || !isset($access_token_info['refreshExpireTime'])) {
                throw new ApiException('refreshaccesstoken错误，或者refreshaccesstoken已失效', 10023);
            }
            // 直接走刷新token的流程
            $refresh_token = $access_token_info['refresh_token'];
            $refreshExpireTime = $access_token_info['refreshExpireTime'];
        }

        $param = http_build_query([
            'appkey' => $this->app_id,
            'grant_type' => 'refresh_token',
            'refresh_token' => $refresh_token,
        ]);

        $result = request_url("https://oauth.open.weidian.com/oauth2/refresh_token?{$param}");
        $result = json_decode($result, true);
        if (!is_array($result)) {
            $result = json_decode($result, true);
        }
        // 校验响应数据是否异常
        wd_check_result_err($result, 'oauth2/refresh_token', []);

        $access_token_info = $result['result'];
        $access_token_info['expire_time'] = time() + ($access_token_info['expire_in'] - 60); // 设置token的过期时间 提前一分钟过期
        $access_token_info['refreshExpireTime'] = $refreshExpireTime;

        // 因为刷新token的话返回的 刷新token过期时间是null，则需要使用之前的时间；
        // 判断token过期时间如果大于缓存的过期时间，则直接使用token的过期时间作为缓存的过期时间
        $calculation_refresh_time = intval($refreshExpireTime / 1000 - 60);
        if ($calculation_refresh_time <= $access_token_info['expire_time']) {
            $calculation_refresh_time = $access_token_info['expire_time'];
        }

        // 重置 AccessToken redis的过期时间集合
        $this->resetExpireAccessTokenRedisList($access_token_info['expire_in'] - 60);

        $cache_key = $this->getCacheKeyByCacheType(WdOpenEnum::CACHE_KEY_ACCESS_TOKEN);
        // 缓存时间以刷新token的时间做缓存，(毫秒 转 秒 - 一分钟)
        $this->setCacheConfig($cache_key, $calculation_refresh_time)
            ->set($access_token_info);

        return $access_token_info;
    }

    /**
     * 获取要过期的token
     * @param $score1
     * @param $score2
     * @return mixed
     * @since: 2023/5/17
     * @author: 陈志洪
     */
    public function getReadyExpireAccessTokens($score1, $score2)
    {
        $access_token_expire_cache_key = $this->getBaseCacheKeyByCacheType(WdOpenEnum::CACHE_KEY_ACCESS_TOKEN_EXPIRE);

        return $this->setCacheConfig($access_token_expire_cache_key)->zrangebyscore($score1, $score2);
    }

    /**
     * 主动刷新token
     * @param $uid
     * @param $openid
     * @return bool
     * @throws \RedisException
     * @author: 陈志洪
     * @since: 2023/5/17
     */
    public function initiativeRefreshToken($uid, $openid)
    {
        // 初始化 cache_key
        $this->reSetUidAndOpenidAndCacheKey(intval($uid), $openid);

        $access_token_info = $this->getAccessToken(false, false);
        // 如果没有则表示该token已经超过30天有效期，必须重新授权
        if (!$access_token_info) {
            return false;
        }

        $this->refreshToken($access_token_info['refresh_token'], $access_token_info['refreshExpireTime']);
        return true;
    }
}
