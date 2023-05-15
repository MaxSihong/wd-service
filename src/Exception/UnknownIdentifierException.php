<?php

declare(strict_types=1);

namespace Maxsihong\WdService\Exception;

/**
 * The identifier of a valid service or parameter was expected.
 *
 * @author Pascal Luna <skalpa@zetareticuli.org>
 */
class UnknownIdentifierException extends \InvalidArgumentException implements NotFoundExceptionInterface
{
    /**
     * @param string $id The unknown identifier
     */
    public function __construct($id)
    {
        parent::__construct(\sprintf('Identifier "%s" is not defined.', $id));
    }
}
