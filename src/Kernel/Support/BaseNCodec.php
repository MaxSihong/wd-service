<?php

declare(strict_types=1);

namespace Maxsihong\WdService\Kernel\Support;

class BaseNCodec
{
    private static $unencodedBlockSize;
    private static $encodedBlockSize;
    protected static int $PAD;
    public static int $pad;
    public static $lineLength;
    private static $chunkSeparatorLength;
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

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct($unencodedBlockSize, $encodedBlockSize, $lineLength, $chunkSeparatorLength, $pad = 61)
    {
        self::$PAD = 61;
        self::$unencodedBlockSize = $unencodedBlockSize;
        self::$encodedBlockSize = $encodedBlockSize;
        $useChunking = $lineLength > 0 && $chunkSeparatorLength > 0;
        self::$lineLength = $useChunking ? $lineLength / $encodedBlockSize * $encodedBlockSize : 0;
        self::$chunkSeparatorLength = $chunkSeparatorLength;
        self::$pad = $pad;
    }

    public function available($context)
    {
        return $context['buffer'] != null ? $context['pos'] - $context['readPos'] : 0;
    }

    protected function getDefaultBufferSize(): int
    {
        return 8192;
    }

    protected static function isWhiteSpace($byteToCheck = 0)
    {
        switch ($byteToCheck) {
            case 9:
            case 10:
            case 13:
            case 32:
                return true;
            default:
                return false;
        }
    }

    public function isInAlphabet($arrayOctet, $allowWSPad = true)
    {
        $var3 = $arrayOctet;
        $var4 = count($arrayOctet);
        for ($var5 = 0; $var5 < $var4; ++$var5) {
            $octet = $var3[$var5];
            if (!$this->isInAlphabet($octet) && (!$allowWSPad || ($octet != $this->pad && !self::isWhiteSpace($octet)))) {
                return false;
            }
        }
        return true;
    }

    protected function containsAlphabetOrPad($arrayOctet)
    {
        if ($arrayOctet == null) {
            return false;
        }
        $var2 = $arrayOctet;
        $var3 = count($arrayOctet);
        for ($var4 = 0; $var4 < $var3; ++$var4) {
            $element = $var2[$var4];
            if (self::$pad == $element || $this->isInAlphabet($element)) {
                return true;
            }
        }
        return false;
    }
}
