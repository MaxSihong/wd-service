<?php

declare(strict_types=1);

namespace Maxsihong\WdService\Kernel\Support;

/**
 * pkcs7偏移处理
 * Class PKCS7Encoder
 * @package App\Models\AESUtils
 */
class PKCS7Encoder
{
    private static int $BLOCK_SIZE = 32;

    /**
     * 进行数据补位
     * @param $count
     * @return array
     */
    public static function encode($count)
    {
        $amountToPad = self::$BLOCK_SIZE - $count % self::$BLOCK_SIZE;
        if ($amountToPad == 0) {
            $amountToPad = self::$BLOCK_SIZE;
        }

        $tmp = [];
        for ($index = 0; $index < $amountToPad; ++$index) {
            $tmp[] = $amountToPad;
        }
        return $tmp;
    }

    /**
     * 去除数组补位数据
     * @param $decrypted
     * @return array
     */
    public static function decode($decrypted)
    {
        $pad = $decrypted[count($decrypted) - 1];
        if ($pad < 1 || $pad > 32) {
            $pad = 0;
        }
        return array_slice($decrypted, 0, count($decrypted) - $pad);
    }
}
