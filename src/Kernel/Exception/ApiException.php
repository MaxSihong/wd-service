<?php

declare(strict_types=1);

namespace Maxsihong\WdService\Kernel\Exception;

class ApiException extends \RuntimeException implements NotFoundExceptionInterface
{
    protected ?array $exceptionData = null;

    public function __construct($message = '', $code = 0, $exceptionData = null, \Throwable $previous = null)
    {
        $this->exceptionData = $exceptionData;
        parent::__construct($message, $code, $previous);
    }

    public function getExceptionData()
    {
        return $this->exceptionData;
    }

    public function __toString()
    {
        return json_encode(array(
            'code' => $this->getCode(),
            'msg' => $this->getMessage(),
            'data' => $this->getExceptionData()
        ), JSON_UNESCAPED_UNICODE);
    }
}
