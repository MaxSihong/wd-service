<?php

declare(strict_types=1);

namespace Maxsihong\WdService\WdService;

use Maxsihong\WdService\Kernel\Exception\ApiException;
use Maxsihong\WdService\Kernel\HttpClient\Client;
use Maxsihong\WdService\Kernel\Support\AESUtils;

class WdCommon extends Client
{
    /**
     * 上传图片
     * @link https://open.weidian.com/#/api/83
     * @param string $file_path 图片路径
     * @return mixed
     * @author: 陈志洪
     * @since: 2023/5/17
     */
    public function uploadFile(string $file_path)
    {
        $access_token = $this->getAccessToken();

        $file_save_url = sys_get_temp_dir() . ('/temp/wd_upload_image/');
        if (file_exists($file_save_url) === false) {
            mkdir($file_save_url, 0777, true);
        }

        // 先将图片转移到本地
        $file_name = md5(time() . round(00000, 99999)) . basename($file_path);
        $image_data = file_get_contents($file_path);
        file_put_contents($file_save_url . $file_name, $image_data);

        $param = [
            "media" => new \CURLFile($file_save_url . $file_name),
            "file_type" => "image",
            "file_name" => $file_name,
            "access_token" => $access_token
        ];

        // 初始化一个 cURL 会话
        $ch = curl_init();
        // 设置 cURL 选项
        curl_setopt($ch, CURLOPT_URL, "https://api.vdian.com/media/upload");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $param);

        // 执行 cURL 会话
        $result = curl_exec($ch);

        // 关闭 cURL 会话
        curl_close($ch);
        $result = json_decode($result, true);
        if (!is_array($result)) {
            $result = json_decode($result, true);
        }
        /**
         * [
        "result" => "https://si.geilicdn.com/open1835604230-1835604230-17d0000001874be144b70a23028d_438_438.jpg"
        "status" => array:2 [
        "status_code" => 0
        "status_reason" => ""
        ]
        ]
         */

        // 校验响应数据是否异常
        try {
            // 校验响应数据是否异常
            wd_check_result_err($result, 'media/upload', []);
        } catch (ApiException $e) {
            // ApiException
            unlink($file_save_url . $file_name);
            // token 过期
            if ($e->getCode() == 10013) {
                // 刷新或重新授权token
                $this->getProvider()->offsetGet('WdAuth')->refreshToken();
                // 重新请求本次业务需要的接口
                return $this->uploadFile($file_path);
            }

            throw new ApiException($e->getMessage(), $e->getCode(), $e->getExceptionData());
        }
        // 删除临时文件
        unlink($file_save_url . $file_name);

        return $result['result'];
    }

    /**
     * 加密
     * @param string $str
     * @return string
     * @author: 陈志洪
     * @since: 2023/5/17
     */
    public function encrypt(string $str): string
    {
        $utils = new AESUtils($this->app_id, $this->app_secret, $this->redis);

        return $utils->encrypt($str);
    }

    /**
     * 解密
     * @param string $str
     * @return string
     * @author: 陈志洪
     * @since: 2023/5/17
     */
    public function decrypt(string $str): string
    {
        $utils = new AESUtils($this->app_id, $this->app_secret, $this->redis);

        return $utils->decrypt($str);
    }
}
