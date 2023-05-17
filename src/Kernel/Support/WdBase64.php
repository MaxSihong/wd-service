<?php

declare(strict_types=1);

namespace Maxsihong\WdService\Kernel\Support;

use Maxsihong\WdService\Kernel\Exception\ApiException;

/**
 * 复制微店加密处理逻辑
 * 详情可见微店jar包内容
 * Class WdBase64
 * @package App\Models\AESUtils
 */
class WdBase64 extends BaseNCodec
{
    private static $STANDARD_ENCODE_TABLE = [65, 66, 67, 68, 69, 70, 71, 72, 73, 74, 75, 76, 77, 78, 79, 80, 81, 82, 83, 84, 85, 86, 87, 88, 89, 90, 97, 98, 99, 100, 101, 102, 103, 104, 105, 106, 107, 108, 109, 110, 111, 112, 113, 114, 115, 116, 117, 118, 119, 120, 121, 122, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 43, 47];
    private static $URL_SAFE_ENCODE_TABLE = [65, 66, 67, 68, 69, 70, 71, 72, 73, 74, 75, 76, 77, 78, 79, 80, 81, 82, 83, 84, 85, 86, 87, 88, 89, 90, 97, 98, 99, 100, 101, 102, 103, 104, 105, 106, 107, 108, 109, 110, 111, 112, 113, 114, 115, 116, 117, 118, 119, 120, 121, 122, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 45, 95];
    private static $DECODE_TABLE = [-1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, 62, -1, 62, -1, 63, 52, 53, 54, 55, 56, 57, 58, 59, 60, 61, -1, -1, -1, -1, -1, -1, -1, 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, -1, -1, -1, -1, 63, -1, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51];
    private $encodeTable = [];
    private $decodeTable;
    private $lineSeparator;
    private $decodeSize;
    private $encodeSize;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct($lineLength, $lineSeparator = [], $urlSafe = false)
    {
        parent::__construct(3, 4, $lineLength, $lineSeparator == null ? 0 : count($lineSeparator));
        $this->decodeTable = self::$DECODE_TABLE;

        if ($lineSeparator != null) {
            if ($this->containsAlphabetOrPad($lineSeparator)) {
                throw new ApiException("lineSeparator不能包含base64字符: [" . $lineSeparator . "]");
            }
            if ($lineLength > 0) {
                $this->encodeSize = 4 + count($lineSeparator);
                $this->lineSeparator = array_fill(0, count($lineSeparator) * 2, 0);
                $this->lineSeparator = arrayCopy($lineSeparator, 0, $this->lineSeparator, 0, count($lineSeparator));
            } else {
                $this->encodeSize = 4;
                $this->lineSeparator = null;
            }
        } else {
            $this->encodeSize = 4;
            $this->lineSeparator = null;
        }

        $this->decodeSize = $this->encodeSize - 1;
        $this->encodeTable = $urlSafe ? self::$URL_SAFE_ENCODE_TABLE : self::$STANDARD_ENCODE_TABLE;
    }

    /**
     * 加密算法
     * @param array $in
     * @param int $inPos
     * @param int $inAvail
     * @param array $context
     * @return array|void
     * @since: 2023/5/17
     * @author: 陈志洪
     */
    public function encodeData(array $in = [], int $inPos = 0, int $inAvail = 0, array $context = [])
    {
        if (!$context['eof']) {
            if ($inAvail < 0) {
                $context['eof'] = true;
                if (0 == $context['modulus'] && self::$lineLength == 0) {
                    return;
                }
                $flag = $context['buffer'] != null && count($context['buffer']) >= $context['pos'] + $this->decodeSize;
                if (!$flag) {
                    if ($context['buffer'] == null) {
                        $context['buffer'] = array_fill(0, $this->getDefaultBufferSize(), 0);
                        $context['pos'] = 0;
                        $context['readPos'] = 0;
                    } else {
                        $context_buffer_length = count($context['buffer']);
                        // 新建默认为0的长度为 （$context_buffer_length * 2） 的数组
                        $b = array_fill(0, $context_buffer_length * 2, 0);
                        $b = arrayCopy($context['buffer'], 0, $b, 0, $context_buffer_length);
                        $context['buffer'] = $b;
                    }
                }
                $buffer = $context['buffer'];
                $savedPos = $context['pos'];
                switch ($context['modulus']) {
                    case 0:
                        break;
                    case 1:
                        $buffer[$context['pos']++] = $this->encodeTable[$context['ibitWorkArea'] >> 2 & 63];
                        $buffer[$context['pos']++] = $this->encodeTable[$context['ibitWorkArea'] << 4 & 63];
                        if ($this->encodeTable == self::$STANDARD_ENCODE_TABLE) {
                            $buffer[$context['pos']++] = self::$pad;
                            $buffer[$context['pos']++] = self::$pad;
                        }
                        break;
                    case 2:
                        $buffer[$context['pos']++] = $this->encodeTable[$context['ibitWorkArea'] >> 10 & 63];
                        $buffer[$context['pos']++] = $this->encodeTable[$context['ibitWorkArea'] >> 4 & 63];
                        $buffer[$context['pos']++] = $this->encodeTable[$context['ibitWorkArea'] << 2 & 63];
                        if ($this->encodeTable == self::$STANDARD_ENCODE_TABLE) {
                            $buffer[$context['pos']++] = self::$pad;
                        }
                        break;
                    default:
                        throw new ApiException("Impossible modulus " . $context['modulus']);
                }
                $context['currentLinePos'] += $context['pos'] - $savedPos;
                if (self::$lineLength > 0 && $context['currentLinePos'] > 0) {
                    $buffer = arrayCopy($this->lineSeparator, 0, $buffer, $context['pos'], count($this->lineSeparator));
                    $context['pos'] += count($this->lineSeparator);
                }
                $context['buffer'] = $buffer;
            } else {
                for ($i = 0; $i < $inAvail; ++$i) {
                    $flag = $context['buffer'] != null && count($context['buffer']) >= $context['pos'] + $this->decodeSize;
                    if (!$flag) {
                        if ($context['buffer'] == null) {
                            $context['buffer'] = array_fill(0, $this->getDefaultBufferSize(), 0);
                            $context['pos'] = 0;
                            $context['readPos'] = 0;
                        } else {
                            $context_buffer_length = count($context['buffer']);
                            // 新建默认为0的长度为 （$context_buffer_length * 2） 的数组
                            $b = array_fill(0, $context_buffer_length * 2, 0);
                            $b = arrayCopy($context['buffer'], 0, $b, 0, $context_buffer_length);
                            $context['buffer'] = $b;
                        }
                    }
                    $buffer = $context['buffer'];
                    $context['modulus'] = ($context['modulus'] + 1) % 3;
                    $b = $in[$inPos++];
                    if ($b < 0) {
                        $b += 256;
                    }
                    $context['ibitWorkArea'] = ($context['ibitWorkArea'] << 8) + $b;
                    if (0 == $context['modulus']) {
                        $buffer[$context['pos']++] = $this->encodeTable[$context['ibitWorkArea'] >> 18 & 63];
                        $buffer[$context['pos']++] = $this->encodeTable[$context['ibitWorkArea'] >> 12 & 63];
                        $buffer[$context['pos']++] = $this->encodeTable[$context['ibitWorkArea'] >> 6 & 63];
                        $buffer[$context['pos']++] = $this->encodeTable[$context['ibitWorkArea'] & 63];
                        $context['currentLinePos'] += 4;
                        if (self::$lineLength > 0 && self::$lineLength <= $context['currentLinePos']) {
                            $buffer = arrayCopy($this->lineSeparator, 0, $buffer, $context['pos'], count($this->lineSeparator));
                            $context['pos'] += count($this->lineSeparator);
                            $context['currentLinePos'] = 0;
                        }
                    }
                    $context['buffer'] = $buffer;
                }
            }
        }
        return $context;
    }

    /**
     * 解密算法
     * @param $in
     * @param $inPos
     * @param $inAvail
     * @param $context
     * @return mixed|null
     * @since: 2023/5/17
     * @author: 陈志洪
     */
    public function decodeData($in = [], $inPos = 0, $inAvail = 0, $context = null)
    {
        if (!$context['eof']) {
            if ($inAvail < 0) {
                $context['eof'] = true;
            }
            for ($i = 0; $i < $inAvail; ++$i) {
                $flag = $context['buffer'] != null && count($context['buffer']) >= $context['pos'] + $this->decodeSize;
                if (!$flag) {
                    if ($context['buffer'] == null) {
                        $context['buffer'] = array_fill(0, $this->getDefaultBufferSize(), 0);;
                        $context['pos'] = 0;
                        $context['readPos'] = 0;
                    } else {
                        $context_buffer_length = count($context['buffer']);
                        // 新建默认为0的长度为 （$context_buffer_length * 2） 的数组
                        $b = array_fill(0, $context_buffer_length * 2, 0);
                        $b = arrayCopy($context['buffer'], 0, $b, 0, $context_buffer_length);
                        $context['buffer'] = $b;
                    }
                }
                $buffer = $context['buffer'];
                $b = $in[$inPos++];
                if ($b == self::$pad) {
                    $context['eof'] = true;
                    break;
                }
                if ($b >= 0 && $b < count(self::$DECODE_TABLE)) {
                    $result = self::$DECODE_TABLE[$b];
                    if ($result >= 0) {
                        $context['modulus'] = ($context['modulus'] + 1) % 4;
                        $context['ibitWorkArea'] = ($context['ibitWorkArea'] << 6) + $result;
                        if ($context['modulus'] == 0) {
                            $buffer[$context['pos']++] = toByte($context['ibitWorkArea'] >> 16 & 255);
                            $buffer[$context['pos']++] = toByte($context['ibitWorkArea'] >> 8 & 255);
                            $buffer[$context['pos']++] = toByte($context['ibitWorkArea'] & 255);
                        }
                    }
                }
                $context['buffer'] = $buffer;
            }

            if ($context['eof'] && $context['modulus'] != 0) {
                $flag = $context['buffer'] != null && count($context['buffer']) >= $context['pos'] + $this->decodeSize;
                if (!$flag) {
                    if ($context['buffer'] == null) {
                        $context['buffer'] = array_fill(0, $this->getDefaultBufferSize(), 0);;
                        $context['pos'] = 0;
                        $context['readPos'] = 0;
                    } else {
                        $context_buffer_length = count($context['buffer']);
                        // 新建默认为0的长度为 （$context_buffer_length * 2） 的数组
                        $b = array_fill(0, $context_buffer_length * 2, 0);
                        $b = arrayCopy($context['buffer'], 0, $b, 0, $context_buffer_length);
                        $context['buffer'] = $b;
                    }
                }
                $buffer = $context['buffer'];
                switch ($context['modulus']) {
                    case 1:
                        break;
                    case 2:
                        $this->validateCharacter(4, $context);
                        $context['ibitWorkArea'] >>= 4;
                        $buffer[$context['pos']++] = toByte($context['ibitWorkArea'] & 255);
                        break;
                    case 3:
                        $this->validateCharacter(2, $context);
                        $context['ibitWorkArea'] >>= 2;
                        $buffer[$context['pos']++] = toByte($context['ibitWorkArea'] >> 8 & 255);
                        $buffer[$context['pos']++] = toByte($context['ibitWorkArea'] & 255);
                        break;
                    default:
                        throw new ApiException("Impossible modulus " . $context['modulus']);
                }
            }
        }
        return $context;
    }

    public function isInAlphabet($octet = '', $allowWSPad = true)
    {
        return $octet >= 0 && $octet < count($this->decodeTable) && $this->decodeTable[$octet] != -1;
    }

    private function validateCharacter($numBitsToDrop, $context)
    {
        if (($context['ibitWorkArea'] & $numBitsToDrop) != 0) {
            throw new ApiException("Last encoded character (before the paddings if any) is a valid base 64 alphabet but not a possible value");
        }
        return ($context['ibitWorkArea'] >> $numBitsToDrop);
    }
}
