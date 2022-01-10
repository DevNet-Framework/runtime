<?php

/**
 * @author      Mohammed Moussaoui
 * @copyright   Copyright (c) Mohammed Moussaoui. All rights reserved.
 * @license     MIT License. For full license information see LICENSE file in the project root.
 * @link        https://github.com/DevNet-Framework
 */

namespace DevNet\System\Linq\Enumerables;

use DevNet\System\Collections\Enumerator;
use DevNet\System\Collections\IEnumerable;
use Closure;

class WhereEnumerable implements IEnumerable
{
    use \DevNet\System\Extension\ExtenderTrait;

    private IEnumerable $Enumerable;
    private array $Array = [];

    public function __construct(IEnumerable $enumerable)
    {
        $this->Enumerable = $enumerable;
    }

    public function where(Closure $predecate)
    {
        $elements = [];
        foreach ($this->Enumerable as $key => $element) {
            if ($predecate($element) !== false) {
                $elements[$key] = $element;
            }
        }

        $this->Array = $elements;
        return $this;
    }

    public function getIterator(): Enumerator
    {
        return new Enumerator($this->Array);
    }
}
