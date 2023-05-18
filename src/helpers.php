<?php

/**
 * 字符串转大驼峰
 * @param string $str
 * @return string
 * @author: 陈志洪
 * @since: 2023/5/15
 */
if (!function_exists('strToGreatHump')) {
    function strToGreatHump(string $str): string
    {
        $value = ucwords(str_replace(array('-', '_'), ' ', $str));
        return str_replace(' ', '', $value);
    }
}

/**
 * 请求网址
 * @param string $url 网址
 * @param string $type 请求类型
 * @param array|string $post_data 数据包
 * @param array $header 头部
 * @param int $time_out 超时时间
 * @return bool|string
 * @author: 陈志洪
 * @since: 2023/5/17
 */
if (!function_exists('request_url')) {
    function request_url(string $url, string $type = 'get', $post_data = [], array $header = array(), int $time_out = 10)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        if (!empty($header)) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        }
        if ($type == 'post') {
            if (is_array($post_data)) {
                $post_data = http_build_query($post_data);
            }
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_USERAGENT, isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '');
        curl_setopt($curl, CURLOPT_TIMEOUT, $time_out);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 信任任何证书
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 检查证书中是否设置域名

        $get_content = curl_exec($curl);
        $error = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if (!empty($error)) {
            $no = curl_errno($curl);
            if ($no && in_array(intval($no), [7, 28], true)) {
                throw new \Maxsihong\WdService\Kernel\Exception\ApiException('网络请求超时，请稍后重试');
            }
        }
        curl_close($curl);

        return $get_content;
    }
}

/**
 * 校验微店相应是否异常
 * @param array|null $result 响应数据
 * @param string $uri uri
 * @param array|string $param 请求参数
 * @return true
 * @author: 陈志洪
 * @since: 2023/5/17
 */
if (!function_exists('wd_check_result_err')) {
    function wd_check_result_err(?array $result, string $uri, $param = '{}'): bool
    {
        if ($result['status']['status_code'] == 0) {
            return true;
        }

        if (in_array($result['status']['status_code'], [10023, 10001, 10013])) {
            throw new \Maxsihong\WdService\Kernel\Exception\ApiException($result['status']['status_reason'], $result['status']['status_code']);
        }

        $err_msg = \Maxsihong\WdService\Enums\WdOpenEnum::STATUS_CODE_MSG[$result['status']['status_code']] ?? $result['status']['status_reason'];

        throw new \Maxsihong\WdService\Kernel\Exception\ApiException($err_msg, 0, [
            'msg' => $err_msg,
            'result' => $result,
            'data' => [
                'url' => $uri,
                'param' => $param,
            ],
        ]);
    }
}

/**
 * string 转 byte[]
 * @desc: 将字符串转换成字节数组
 * @param string $string 处理字符串
 * @return array
 * @author: 陈志洪
 * @since: 2023/5/17
 */
if (!function_exists('getBytes')) {
    function getBytes(string $string): array
    {
        $bytes = array();
        for ($i = 0; $i < strlen($string); $i++) {    //遍历每一个字符 用ord函数把它们拼接成一个php数组
            if (ord($string[$i]) >= 128) {
                $byte = ord($string[$i]) - 256;
            } else {
                $byte = ord($string[$i]);
            }
            $bytes[] = $byte;
        }
        return $bytes;
    }
}

/**
 * string 转 byte[]
 * @desc: 将字符转为byte
 * @param int $num 处理字符串
 * @return float|int
 * @author: 陈志洪
 * @since: 2023/5/17
 */
if (!function_exists('toByte')) {
    function toByte(int $num)
    {
        $num = decbin($num);    // 转2进制
        $num = substr($num, -8); //取后8位
        $sign = $num[0]; //获取高位符
        if ($sign == 1 && strlen($num) == 8) { //高位是1 代表是负数 ,则要减去256
            return bindec($num) - 256;
        }
        return bindec($num);
    }
}

/**
 * 替换数组内容
 * @param array $src 被替换的数组
 * @param int $srcPos 从$src数组哪个下标开始替换
 * @param array $dest 当前需要返回的数组
 * @param int $destPos 数组开始位置
 * @param int $length 结束位置
 * @return array
 * @author: 陈志洪
 * @since: 2023/5/17
 */
if (!function_exists('arrayCopy')) {
    function arrayCopy(array $src = [], int $srcPos = 0, array $dest = [], int $destPos = 0, int $length = 0): array
    {
        $src = array_slice($src, $srcPos, $length);
        $length_end = $destPos + $length;
        for ($i = $destPos; $i < $length_end; $i++) {
            $dest[$i] = $src[$i - $destPos];
        }
        unset($value);
        return $dest;
    }
}

/**
 * 从数组中获取某些数据
 * @param array $list
 * @param array $fields
 * @return array
 * @since: 2023/5/17
 * @author: 陈志洪
 */
if (!function_exists('arrayListOnly')) {
    function arrayListOnly(array $list, array $fields)
    {
        if (empty($list)) {
            return $list;
        }

        $arr = [];
        foreach ($fields as $field) {
            if (isset($list[$field])) {
                $arr[$field] = $list[$field];
            }
        }

        return $arr;
    }
}
