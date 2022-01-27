<?php
/**
 * @author      Mohammed Moussaoui
 * @copyright   Copyright (c) Mohammed Moussaoui. All rights reserved.
 * @license     MIT License. For full license information see LICENSE file in the project root.
 * @link        https://github.com/DevNet-Framework
 */

namespace DevNet\System\Logging\Console;

use DateTime;
use DevNet\System\IO\Console;
use DevNet\System\IO\ConsoleColor;
use DevNet\System\Logging\ILogger;
use DevNet\System\Logging\LogLevel;

class ConsoleLogger implements ILogger
{
    private string $Category;
    
    public function __construct(string $category)
    {
        $this->Category = $category;
    }

    public function log(int $level, string $message, array $args = []): void
    {
        switch ($level) {
            case LogLevel::Trace:
                $severity = 'Trace: ';
                break;
            case LogLevel::Debug:
                Console::foregroundColor(ConsoleColor::Blue);
                $severity = 'Debug: ';
                break;
            case LogLevel::Information:
                Console::foregroundColor(ConsoleColor::Green);
                $severity = 'Info: ';
                break;
            case LogLevel::Warning:
                Console::foregroundColor(ConsoleColor::Yellow);
                $severity = 'Warn: ';
                break;
            case LogLevel::Error:
                Console::foregroundColor(ConsoleColor::Red);
                $severity = 'Error: ';
                break;
            case LogLevel::Fatal:
                Console::foregroundColor(ConsoleColor::White);
                Console::backgroundColor(ConsoleColor::Red);
                $severity = 'Fatal: ';
                break;
            default:
                return;
                break;
        }

        $dateTime = DateTime::createFromFormat('U.u', microtime(TRUE));
        $date = '[' . $dateTime->format('Y-M-d H:i:s.v') . '] ';

        $replace = [];
        foreach ($args as $key => $value) {
            // map the arguments if the value can be casted to string
            if (!is_array($value) && (!is_object($value) || method_exists($value, '__toString'))) {
                $replace['{' . $key . '}'] = $value;
            }
        }

        // interpolate replacement values into the string format
        $message = strtr($message, $replace);

        Console::writeLine($date . $this->Category);
        Console::writeLine('    '. $severity. $message);
        Console::resetColor();
    }
}
