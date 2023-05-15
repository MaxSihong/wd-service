<?php

namespace Maxsihong\WdService\Exception;

/**
 * An attempt to perform an operation that requires a service identifier was made.
 *
 * @author Pascal Luna <skalpa@zetareticuli.org>
 */
class InvalidServiceIdentifierException extends \InvalidArgumentException implements NotFoundExceptionInterface
{
    /**
     * @param string $id The invalid identifier
     */
    public function __construct($id)
    {
        parent::__construct(\sprintf('Identifier "%s" does not contain an object definition.', $id));
    }
}
