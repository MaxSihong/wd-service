<?php

declare(strict_types=1);

namespace Maxsihong\WdService\Kernel\Contracts;

interface ClientInterface
{
    public function api(string $uri);
}