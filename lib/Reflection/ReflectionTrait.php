<?php

/**
 * @author      Mohammed Moussaoui
 * @copyright   Copyright (c) Mohammed Moussaoui. All rights reserved.
 * @license     MIT License. For full license information see LICENSE file in the project root.
 * @link        https://github.com/DevNet-Framework
 */

namespace DevNet\System\Reflection;

use DevNet\System\Type;

trait ReflectionTrait
{
    private ?Type $type = null;

    public function getType(): Type
    {
        if (!$this->type) {
            $this->type = new Type(get_class($this));
        }

        return $this->type;
    }
}