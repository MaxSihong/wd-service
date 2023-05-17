<?php

declare(strict_types=1);

namespace Maxsihong\WdService\Kernel\Support;

/**
 * 微店注入方法,远程调用获取微店密钥
 * Class PKCS7Encoder
 * @package App\Models\AESUtils
 */
class SecurityHolder
{
    /**
     * 微店获取解密key值(远程api获取)
     * 写入缓存十分钟
     * @param string $appkey
     * @param string $secret
     * @param \Redis|null $redis_client
     * @param bool $clear
     * @return bool|float|int|mixed|\Redis|string
     * @throws \RedisException
     * @author: 陈志洪
     * @since: 2023/5/17
     */
    public static function doInit(string $appkey, string $secret, \Redis $redis_client = null, bool $clear = false)
    {
        $key = "ONLINE_RETAILERS_wd:DOINIT_APPKEY_SECRET";

        // 删除
        if ($clear) {
            $redis_client->del($key);
            return true;
        }

        // 获取缓存标识
        $data = $redis_client->get($key);
        if ($data && strpos($data, 'think_serialize:') !== false) {
            $data = unserialize(substr($data, 16));
        }

        // 若缓存存在时, 则不进行添加
        if (!$data) {
            $uri = 'open.secret.list';
            $publicJson = [
                'method' => $uri,
                'format' => 'json',
                'version' => '1.0'
            ];
            $paramJson = [
                'app_key' => $appkey,
                'secret' => $secret,
            ];
            $param = [
                'public' => json_encode($publicJson, 256),
                'param' => json_encode($paramJson, 256),
            ];
            $param = urldecode(http_build_query($param));
            // 设置头部
            $header[] = 'Accept:application/json';
            // 拼接请求鉴权参数
            $result = request_url('https://api.vdian.com/api', 'post', $param, $header, 20);
            $result = json_decode($result, true);
            if (!is_array($result)) {
                $result = json_decode($result, true);
            }

            // 校验响应数据是否异常
            wd_check_result_err($result, $uri, $paramJson);

            $data = $result['result'][0]['aesKey'];
            $data = is_scalar($data) ? $data : 'think_serialize:' . serialize($data);
            $redis_client->setex($key, 600, $data);
        }

        return $data;
    }
}
