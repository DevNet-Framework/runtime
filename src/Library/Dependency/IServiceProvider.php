<?php declare(strict_types = 1);
/**
 * @author      Mohammed Moussaoui
 * @copyright   Copyright (c) Mohammed Moussaoui. All rights reserved.
 * @license     MIT License. For full license information see LICENSE file in the project root.
 * @link        https://github.com/DevNet-Framework
 */

namespace DevNet\System\Dependency;

/**
 * Describes the interface of a container that exposes methods to read its entries.
 */
interface IServiceProvider
{
    /**
     * Finds an entry of the container by its identifier and returns it.
     */
    public function getService(string $serviceType);

    public function has(string $serviceType);
}
