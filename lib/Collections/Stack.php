<?php

/**
 * @author      Mohammed Moussaoui
 * @copyright   Copyright (c) Mohammed Moussaoui. All rights reserved.
 * @license     MIT License. For full license information see LICENSE file in the project root.
 * @link        https://github.com/DevNet-Framework
 */

namespace DevNet\System\Collections;

use DevNet\System\Type;
use DevNet\System\Text\StringBuilder;
use DevNet\System\Exceptions\TypeException;
use DevNet\System\Exceptions\ErrorMessageExtension;

class Stack implements IEnumerable
{
    use \DevNet\System\Collections\GenericTrait;
    use \DevNet\System\Extension\ExtenderTrait;

    private array $array = [];
    private Type $genericType;

    public function __construct(string $valueType)
    {
        $this->setTypeParameters([$valueType]);
    }

    public function push($value): void
    {
        $genericArgs = $this->getType()->getGenericArguments();
        if (!$genericArgs[0]->isOfType($value)) {
            $className = get_class($this);
            throw new TypeException("The value passed to {$className} must be of the type {$genericArgs[1]}");
        }

        $this->array[$value];
    }

    public function pop()
    {
        return array_pop($this->array);
    }

    public function peek()
    {
        return end($this->array);
    }

    public function contains($item): bool
    {
        return in_array($item, $this->array);
    }

    public function remove($item): void
    {
        if (isset($this->array[$item])) {
            unset($this->array[$item]);
        }
    }

    public function clear(): void
    {
        $this->array = [];
    }

    public function getIterator(): Enumerator
    {
        return new Enumerator($this->array);
    }

    public function toArray(): array
    {
        return $this->array;
    }
}
