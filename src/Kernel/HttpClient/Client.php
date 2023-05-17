<?php

declare(strict_types=1);

namespace Maxsihong\WdService\Kernel\HttpClient;

use Maxsihong\WdService\Enums\WdOpenEnum;
use Maxsihong\WdService\Kernel\Command\BaseClient;
use Maxsihong\WdService\Kernel\Container;
use Maxsihong\WdService\Kernel\Exception\ApiException;
use Maxsihong\WdService\Kernel\Contracts\ClientInterface;

class Client extends BaseClient implements ClientInterface
{
    private string $request_type = 'post';

    private array $result_status = [];
    private array $result_data = [];

    private int $uid = 0;

    protected array $wd_merchant_info = [];

    /**
     * @var string 商家ID
     */
    private string $openid = '';

    public function __construct(Container $provider)
    {
        parent::__construct($provider);

        $this->setUid();
        $this->setOpenid();

        $openid = $this->getOpenid() ? "_{$this->getOpenid()}" : '';
        $this->cache_key .= "{$this->getUid()}{$openid}:";
    }

    public function api(string $uri, $version = '1.0', $post_data = '{}', $header = [])
    {
        $time_out = $this->timeout;
        $access_token = $this->getAccessToken();

        if (empty($post_data)) {
            $post_data = '{}';
        }

        // 判断原有链接是否存在 参数
        $param = [
            'public' => json_encode([ // 公用参数
                'method' => $uri,
                'access_token' => $access_token,
                'format' => 'json',
                'version' => $version,
            ], JSON_UNESCAPED_UNICODE),
            'param' => is_array($post_data) ? json_encode($post_data, 256) : $post_data,
        ];

        $ref_req = compact('uri', 'version', 'post_data', 'header', 'time_out');

        $param = urldecode(http_build_query($param));

        // 设置头部
        $header[] = 'Accept:application/json';

        // 拼接请求鉴权参数
        $result = request_url($this->domain, $this->request_type, $param, $header, $time_out);

        $result = json_decode($result, true);
        if (!is_array($result)) {
            $result = json_decode($result, true);
        }

        return $this->doResult($uri, $param, $result, $ref_req);
    }

    protected function getAccessToken(bool $only_token = true)
    {
        return $this->getProvider()->offsetGet('WdAuth')->getAccessToken($only_token);
    }

    private function doResult($uri, $param, $result, $ref_req)
    {
        // 校验响应数据是否异常
        try {
            wd_check_result_err($result, $uri, $param);
        } catch (ApiException $e) {
            // token 过期
            if ($e->getCode() == 10013) {
                // 刷新或重新授权token
                $this->getProvider()->offsetGet('WdAuth')->refreshToken();
                // 重新请求本次业务需要的接口
                return $this->api($ref_req['uri'], $ref_req['version'], $ref_req['post_data'], $ref_req['header'], $ref_req['time_out']);
            }

            // 10023 refreshaccesstoken错误，或者refreshaccesstoken已失效 判断特殊错误码给客户端

            throw new ApiException($e->getMessage(), $e->getCode(), $e->getExceptionData());
        }

        $this->result_status = $result['status'];
        unset($result['status']);
        if (is_bool($result['result'])) {
            $this->result_data = [];
        } else {
            $this->result_data = $result['result'] ?? $result; // 兼容部分接口返回结果没有result的响应（如商品上下架）
        }

        return $this->result_data;
    }

    /**
     * 设置请求类型 post、get
     * @param string $request_type
     * @return $this
     */
    protected function setRequestType(string $request_type): self
    {
        $this->request_type = $request_type;
        return $this;
    }

    /**
     * 获取当前请求状态数据
     * @return array
     */
    protected function getResultStatus(): array
    {
        return $this->result_status;
    }

    /**
     * 获取当前请求相应数据
     * @return array
     */
    protected function getResultData(): array
    {
        return $this->result_data;
    }

    private function setUid($uid = 0): void
    {
        $this->uid = $uid ?: $this->getExtend()['uid'] ?? 0;
    }

    /**
     * @return int
     */
    protected function getUid(): int
    {
        return $this->uid;
    }

    /**
     * @return string
     */
    protected function getOpenid(): string
    {
        return $this->openid;
    }

    private function setOpenid($openid = ''): void
    {
        $this->openid = $openid ?: $this->getExtend()['openid'] ?? '';
    }

    /**
     * 根据 全局的缓存key 拼接传入类型
     * @param string $cache_type
     * @return string
     * @author: 陈志洪
     * @since: 2023/5/17
     */
    protected function getCacheKeyByCacheType(string $cache_type): string
    {
        return $this->cache_key . $cache_type;
    }

    /**
     * 根据 基础的缓存key 拼接传入类型
     * @param string $cache_type
     * @return string
     * @author: 陈志洪
     * @since: 2023/5/17
     */
    protected function getBaseCacheKeyByCacheType(string $cache_type): string
    {
        return $this->base_cache_key . $cache_type;
    }

    /**
     * 重置 AccessToken redis的过期时间集合
     * @param int $expire_in
     * @param bool $reset 是否重置redis集合
     * @return bool
     * @since: 2023/5/17
     * @author: 陈志洪
     */
    protected function resetExpireAccessTokenRedisList(int $expire_in, bool $reset = true): bool
    {
        $member = "{$this->getUid()}_{$this->getOpenid()}";
        $access_token_expire_cache_key = $this->getBaseCacheKeyByCacheType(WdOpenEnum::CACHE_KEY_ACCESS_TOKEN_EXPIRE);

        // 重置 AccessToken redis的过期时间集合
        $this
            ->setCacheConfig($access_token_expire_cache_key)
            ->zrem($member);

        if ($reset) {
            $this
                ->setCacheConfig($access_token_expire_cache_key)
                ->zadd($expire_in, $member);
        }

        return true;
    }

    protected function reSetCacheKey()
    {
        $this->cache_key = "{$this->getBaseCacheKey()}{$this->getUid()}_{$this->getOpenid()}:";
    }

    /**
     * 重置uid、openid、CacheKey
     * @param int $uid
     * @param string $openid
     * @param bool $is_re_provider 是否重置容器内的数据
     * @since: 2023/5/17
     * @author: 陈志洪
     */
    public function reSetUidAndOpenidAndCacheKey(int $uid = 0, string $openid = '', bool $is_re_provider = true)
    {
        if ($uid) {
            $this->setUid($uid);
        }
        if ($openid) {
            $this->setOpenid($openid);
        }

        $this->reSetCacheKey();

        $driver = $this->getProvider()->offsetGet('driver');
        $driver_ucf = ucfirst($driver); // 首字母大写

        if (!$is_re_provider) {
            return;
        }

        $folders = glob(dirname(__DIR__, 2) . '/Wd*');

        foreach ($folders as $folder) {
            $model = basename($folder);
            $files = glob($folder . "/*.php");

            $object = new \ArrayObject();
            foreach ($files as $file) {
                $class_name = basename($file, '.php'); // 截取获取到类名称

                // 同时修改容器内的key
                $this->getProvider()
                    ->offsetGet($class_name)->getProvider()
                    ->offsetSet('extend', [
                        'uid' => $this->uid,
                        'openid' => $this->openid
                    ]);

                // 同时重置容器内的 cache_key
                $this->getProvider()
                    ->offsetGet($class_name)
                    ->reSetUidAndOpenidAndCacheKey($uid, $openid, false);
            }
        }
    }
}
