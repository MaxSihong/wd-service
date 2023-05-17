<?php

declare(strict_types=1);

namespace Maxsihong\WdService\Kernel\Contracts;

/**
 * Pimple service provider interface.
 *
 * @author  Fabien Potencier
 * @author  Dominik Zogg
 */
interface ServiceProviderInterface
{
    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     */
    public function register(\Maxsihong\WdService\Kernel\Container $pimple);
}
