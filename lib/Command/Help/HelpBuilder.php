<?php

/**
 * @author      Mohammed Moussaoui
 * @copyright   Copyright (c) Mohammed Moussaoui. All rights reserved.
 * @license     MIT License. For full license information see LICENSE file in the project root.
 * @link        https://github.com/DevNet-Framework
 */

namespace DevNet\System\Command\Help;

use DevNet\System\Command\CommandLine;

class HelpBuilder
{
    private CommandLine $command;
    private int $primaryColor = 7;
    private int $secondaryColor = 6;
    private array $layouts = [];


    public function __construct(CommandLine $command)
    {
        $this->command = $command;
    }

    public function setColor(int $primaryColor, int $secondaryColor): void
    {
        $this->primaryColor = $primaryColor;
        $this->secondaryColor = $secondaryColor;
    }

    public function useDefaults(): void
    {
        $this->writeDescription();
        $this->writeUsage();
        $this->writeArguments();
        $this->writeOptions();
        $this->writeCommands();
    }

    public function writeHeading(string $title)
    {
        $this->layouts[] = ['type' => 'heading', 'content' => $title];
    }

    public function writeLine(string $value = '')
    {
        $this->layouts[] = ['type' => 'line', 'content' => $value];
    }

    public function writeRows(array $rows): void
    {
        ksort($rows);
        $max = 0;
        $lines = [];
        foreach ($rows as $name => $description) {
            $lenth = strlen($name);
            if ($lenth > $max) {
                $max = $lenth;
            }
        }

        foreach ($rows as $name => $description) {
            $lenth = strlen($name);
            $space = str_repeat(" ", $max - $lenth + 3);
            $lines["  {$name}{$space}"] = $description;
        }

        $this->layouts[] = ['type' => 'rows', 'content' => $lines];
    }

    public function writeDescription(): void
    {
        $this->writeHeading('Description:');
        $this->writeLine("  {$this->command->getDescription()}");
        $this->writeLine();
    }

    public function writeUsage(): void
    {
        $this->writeHeading("Usage:");
        $usage = '';

        $parents = [];
        $command = $this->command;

        while ($command->getParent()) {
            $parents[] = $command->getParent();
            $command = $command->getParent();
        }

        $parents = array_reverse($parents);

        foreach ($parents as $parent) {
            $usage .= ' ';
            $usage .= $parent->getName();
        }

        $usage .= ' ';
        $usage .= $this->command->getName();

        if ($this->command->getArguments()) {
            $usage .= ' ';
            $usage .= '[arguments]';
        }

        if ($this->command->getCommands()) {
            $usage .= ' ';
            $usage .= '[command]';
        }

        if ($this->command->getOptions()) {
            $usage .= ' ';
            $usage .= '[options]';
        }

        $this->writeline($usage);
        $this->writeline();
    }

    public function writeArguments(): void
    {
        if ($this->command->getArguments()) {
            $this->writeHeading("Arguments:");
            $rows = [];
            foreach ($this->command->getArguments() as $argument) {
                $rows[$argument->getName()] = $argument->getDescription();
            }
            $this->writeRows($rows);
            $this->writeline();
        }
    }

    public function writeOptions(): void
    {
        if ($this->command->getOptions()) {
            $this->writeHeading("Options:");
            $rows = [];
            foreach ($this->command->getOptions() as $option) {
                $name = $option->getName();
                if ($option->getAlias()) {
                    $name .= ', ' . $option->getAlias();
                }
                $rows[$option->getName()] = $option->getDescription();
            }
            $this->writeRows($rows);
            $this->writeline();
        }
    }

    public function writeCommands(): void
    {
        if ($this->command->getCommands()) {
            $this->writeHeading("Commands:");
            $rows = [];
            foreach ($this->command->getCommands() as $command) {
                $rows[$command->getName()] = $command->getDescription();
            }
            $this->writeRows($rows);
            $this->writeline();
        }
    }

    public function build(): HelpResult
    {
        return new HelpResult($this->layouts, $this->primaryColor, $this->secondaryColor);
    }
}