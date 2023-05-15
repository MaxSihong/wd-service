<?php

declare(strict_types=1);

namespace Maxsihong\WdService\Exception;

/**
 * An attempt to modify a frozen service was made.
 *
 * @author Pascal Luna <skalpa@zetareticuli.org>
 */
class FrozenServiceException extends \RuntimeException implements NotFoundExceptionInterface
{
    /**
     * @param string $id Identifier of the frozen service
     */
    public function __construct($id)
    {
        parent::__construct(\sprintf('Cannot override frozen service "%s".', $id));
    }
}
