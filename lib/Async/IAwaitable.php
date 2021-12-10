<?php

/**
 * @author      Mohammed Moussaoui
 * @copyright   Copyright (c) Mohammed Moussaoui. All rights reserved.
 * @license     MIT License. For full license information see LICENSE file in the project root.
 * @link        https://github.com/DevNet-Framework
 */

namespace DevNet\System\Async;

interface IAwaitable
{
    /**
     * return wait handle of type IAwaiter.
     */
    public function getAwaiter(): IAwaiter;
}
