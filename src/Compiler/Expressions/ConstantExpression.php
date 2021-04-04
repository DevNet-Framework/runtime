<?php declare(strict_types = 1);
/**
 * @author      Mohammed Moussaoui
 * @copyright   Copyright (c) Mohammed Moussaoui. All rights reserved.
 * @license     MIT License. For full license information see LICENSE file in the project root.
 * @link        https://github.com/DevNet-Framework
 */

namespace DevNet\System\Compiler\Expressions;

use DevNet\System\Compiler\ExpressionVisitor;

class ConstantExpression extends Expression
{
    public $value;
    public ?string $Type;

    public function __construct($value, ?string $type = null)
    {
        $this->Value = $value;
        $this->Type = $type;
    }

    public function accept(ExpressionVisitor $visitor)
    {
        $visitor->visitConstant($this);
    }
}
