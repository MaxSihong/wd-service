<?php

declare(strict_types=1);

namespace Maxsihong\WdService\Kernel\Support;

use Maxsihong\WdService\Kernel\Exception\ApiException;

class AESUtils
{
    /**
     * @var array
     */
    private static array $context = [
        'ibitWorkArea' => 0,
        'lbitWorkArea' => 0,
        'buffer' => null,
        'pos' => 0,
        'readPos' => 0,
        'eof' => false,
        'currentLinePos' => 0,
        'modulus' => 0
    ];

    // 微店加密key
    private string $app_key = '';

    // 微店密钥
    private string $secret = '';

    private \Redis $redis_client;

    public function __construct($app_key, $secret, \Maxsihong\WdService\Kernel\Support\Redis $redis)
    {
        $this->app_key = $app_key;
        $this->secret = $secret;
        $this->redis_client = $redis->getRedisConnect();
    }

    /**
     * 微店Java加密通信
     * @param string $data 加密内容
     * @return string
     */
    public function encrypt(string $data): string
    {
        try {
            // 获取解密key
            $key = SecurityHolder::doInit($this->app_key, $this->secret, $this->redis_client);
            // 获取当前加密内容字节数组
            $textBytes = getBytes($data);
            // 根据加密内容进行补位
            $padBytes = PKCS7Encoder::encode(count($textBytes));
            // 合并补位数据
            $data_message = array_merge($textBytes, $padBytes);
            // 转成字节字符串给与openssl_encrypt进行加密
            $data_message = vsprintf(str_repeat('%c', count($data_message)), $data_message);
            // 处理key值
            $keys = $this->string2Byte($key);
            $keys = base64_encode(vsprintf(str_repeat('%c', count($keys)), $keys));
            // 根据key值获取iv值,偏移16位
            $iv = array_slice($this->decodeBase64($key), 0, 16);
            // 处理iv值转成字符串
            $iv = base64_encode(vsprintf(str_repeat('%c', count($iv)), $iv));
            // 加密数据 返回字节码
            $encrypt_message = openssl_encrypt(
                $data_message,
                "aes-128-cbc",
                base64_decode($keys),
                OPENSSL_NO_PADDING,
                base64_decode($iv)
            );
            // 处理成字节数组
            $encrypt_message = getBytes($encrypt_message);
            // 转成字节字符串,出现乱码需base64返回数据
            return base64_encode(vsprintf(str_repeat('%c', count($encrypt_message)), $encrypt_message));
        } catch (\Exception $e) {
            throw new ApiException('加密处理失败');
        }
    }

    /**
     * 批量加密
     * @param array $data
     * @return array
     * @author: 陈志洪
     * @since: 2023/5/17
     */
    public function encryptBatch(array $data = []): array
    {
        if (!$data) {
            return [];
        }
        $encrypt = [];
        foreach ($data as $key => $val) {
            $encrypt[] = [$key => $this->encrypt($val)];
        }
        return $encrypt;
    }

    /**
     * 微店Java解密通信
     * @param string $string 加密内容
     * @return string
     * @author: 陈志洪
     * @since: 2023/5/17
     */
    public function decrypt(string $string): string
    {
        try {
            // 获取解密key
            $key = SecurityHolder::doInit($this->app_key, $this->secret, $this->redis_client);

            // 处理key值
            $keys = $this->string2Byte($key);
            $keys = vsprintf(str_repeat('%c', count($keys)), $keys);
            // base64转换成字节再转化成字节数组
            $desc = getBytes(base64_decode($string));
            // 字节数组转字符串
            $decrypt = vsprintf(str_repeat('%c', count($desc)), $desc);
            // 根据key值获取iv值,偏移16位
            $aesKey = array_slice($this->decodeBase64($key), 0, 16);
            // 处理iv值转成字符串
            $iv = base64_encode(vsprintf(str_repeat('%c', count($aesKey)), $aesKey));
            // 解密返回字节字符串
            $decrypt_message = openssl_decrypt(
                $decrypt,
                "aes-128-cbc",
                $keys,
                OPENSSL_NO_PADDING,
                base64_decode($iv)
            );
            // 获取当前转义的字符串转字节数组
            $decrypt_message = getBytes($decrypt_message);
            // 去除pkcs7补位数据
            $decrypt_message = PKCS7Encoder::decode($decrypt_message);
            // 字节数组转字符串
            return vsprintf(str_repeat('%c', count($decrypt_message)), $decrypt_message);
        } catch (\Exception $e) {
            throw new ApiException('解密数据失败');
        }
    }

    /**
     * 批量解密
     * @param array $source
     * @return array
     * @author: 陈志洪
     * @since: 2023/5/17
     */
    public function decryptBatch(array $source = []): array
    {
        if (!$source) {
            return [];
        }
        $decrypt = [];
        foreach ($source as $key => $val) {
            $decrypt[] = [$key => $this->decrypt($val)];
        }
        return $decrypt;
    }

    /**
     * 处理成16字节数组
     * @param string $str
     * @return array
     */
    public function string2Byte(string $str): array
    {
        $result = [];
        $count = strlen($str) / 2;
        for ($i = 0; $i < $count; ++$i) {
            $b = toByte(($this->getStrIndex($str[2 * $i]) & 15) << 4 | $this->getStrIndex($str[2 * $i + 1]) & 15);
            $result[$i] = $b;
        }
        return $result;
    }

    /**
     * 获取当前字节下标
     * @param $c
     * @return int
     * @author: 陈志洪
     * @since: 2023/5/17
     */
    public function getStrIndex($c): int
    {
        return ord($c) > ord('9') ? 10 + (ord($c) - 97) : ord($c) - 48;
    }

    /**
     * 数据进行加密处理,获取iv偏移
     * @param $base64String
     * @return array|mixed
     * @author: 陈志洪
     * @since: 2023/5/17
     */
    public function decodeBase64($base64String)
    {
        if (is_string($base64String)) {
            $base64String = getBytes($base64String);
        }
        if ($base64String != null && $base64String != 0) {
            $context = self::$context;
            $context = (new WdBase64(0, [13, 10], false))->decodeData($base64String, 0, count($base64String), $context);
            $context = (new WdBase64(0, [13, 10], false))->decodeData($base64String, 0, -1, $context);
            $result = array_fill(0, $context['pos'], 0);
            if ($context['buffer'] != null) {
                $len = min(($context['buffer'] != null ? $context['pos'] - $context['readPos'] : 0), count($result));
                $result = arrayCopy($context['buffer'], $context['readPos'], $result, 0, $len);
                $context['readPos'] += $len;
                if ($context['readPos'] >= $context['pos']) {
                    $context['buffer'] = null;
                }
            }
            return $result;
        }
        return $base64String;
    }
}
