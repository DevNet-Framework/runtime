<?php

/**
 * @author      Mohammed Moussaoui
 * @copyright   Copyright (c) Mohammed Moussaoui. All rights reserved.
 * @license     MIT License. For full license information see LICENSE file in the project root.
 * @link        https://github.com/DevNet-Framework
 */

namespace DevNet\System\Async;

use DevNet\System\Action;
use DevNet\System\Exceptions\ArrayException;
use DevNet\System\ObjectTrait;
use Closure;

class Task implements IAwaitable
{
    use ObjectTrait;

    public const Created   = 0;
    public const Pending   = 1;
    public const Running   = 2;
    public const Succeeded = 3;
    public const Canceled  = -1;
    public const Failed    = -2;

    private int $id;
    private int $status = 0;
    private TaskScheduler $scheduler;
    private ?Task $continuationTask = null;
    private ?IAwaiter $awaiter = null;
    private ?CancelationToken $token = null;
    private bool $isCompleted = false;
    private $result = null;

    public function __construct(Closure $action = null, ?CancelationToken $token = null)
    {
        $this->id        = spl_object_id($this);
        $this->status    = Self::Succeeded;
        $this->scheduler = TaskScheduler::getDefaultScheduler();
        $this->awaiter   = new TaskAwaiter();
        $this->token     = $token;

        if ($action) {
            $this->status = Self::Created;
            $action = new Action($action);
            if ($action->Function->isGenerator()) {
                $this->awaiter = new TaskAwaiter($action(), $token);
            } else {
                $function = function () use ($action) {
                    return yield $action();
                };
                $this->awaiter = new TaskAwaiter($function(), $token);
            }
        }
    }

    public function get_Id(): int
    {
        return $this->id;
    }

    public function get_Status(): int
    {
        return $this->status;
    }

    public function get_IsCompleted(): bool
    {
        if (!$this->isCompleted && $this->status == Task::Running) {
            if ($this->awaiter->IsCompleted()) {
                $this->wait();
            }
        }

        return $this->isCompleted;
    }

    public function get_Result()
    {
        if (!$this->isCompleted) {
            $this->wait();
        }

        return $this->result;
    }

    public function getAwaiter(): IAwaiter
    {
        return $this->awaiter;
    }

    public function start(TaskScheduler $taskScheduler = null): void
    {
        if ($this->status == Task::Running || $this->isCompleted) {
            return;
        }

        if ($taskScheduler) {
            $this->scheduler = $taskScheduler;
        }

        $this->status = Self::Pending;
        $continuationAction = $this->awaiter->OnCompleted;

        if ($this->scheduler->MaxConcurrency == 0 || $this->scheduler->MaxConcurrency - count($this->scheduler->getScheduledTasks()) > 0) {
            $this->status = Task::Running;
        }

        if ($continuationAction) {
            $this->awaiter->onCompleted($continuationAction);
        }

        $this->scheduler->enqueue($this);
    }

    public function wait(): void
    {
        if ($this->isCompleted) {
            return;
        }

        if ($this->status == Task::Created || $this->status == Task::Pending) {
            $this->status = Task::Running;
        }

        if ($this->status == Task::Running) {
            try {
                $this->isCompleted = true;
                $this->result = $this->awaiter->getResult();
                $this->scheduler->dequeue($this);
            } catch (\Throwable $exception) {
                if ($exception instanceof CancelationException) {
                    $this->status = self::Canceled;
                    throw $exception;
                }

                $this->status = self::Failed;
                throw $exception;
            }

            $this->status = Task::Succeeded;
        }
    }

    public function then(Closure $continuationAction, ?CancelationToken $token = null): Task
    {
        $previousTask = $this;
        $continuationTask = new Task(function () use ($continuationAction, $previousTask) {
            yield $previousTask;
            return $continuationAction($previousTask);
        }, $token);

        $this->awaiter->onCompleted(function () use ($continuationTask) {
            $continuationTask->wait();
        });

        return $continuationTask;
    }

    public static function run(Closure $action, ?CancelationToken $token = null): Task
    {
        $task = new Task($action, $token);
        $task->start();
        return $task;
    }

    public static function delay(float $seconds): Task
    {
        $task = new Task(function () use ($seconds) {
            $startTime = microtime(true);
            do {
                $elapsedTime = yield microtime(true) - $startTime;
            } while ($elapsedTime < $seconds);
            return true;
        });
        $task->start();
        return $task;
    }

    public static function fromResult($result): Task
    {
        $task = new Task(function () use ($result) {
            return $result;
        });
        $task->wait();
        return $task;
    }

    public static function fromException(string $message, int $code = 0): Task
    {
        $task = new Task(function () use ($message, $code) {
            return new TaskException($message, $code);
        });
        $task->wait();
        return $task;
    }

    public static function completedTask(): Task
    {
        return new Task();
    }

    public static function waitAll(array $tasks): void
    {
        while ($tasks) {
            foreach ($tasks as $index => $task) {
                if (!$task instanceof Task) {
                    throw new ArrayException("The Item of the index {$index}, must be of type: " . Task::class);
                }
                if ($task->getAwaiter()->IsCompleted()) {
                    $task->getAwaiter()->getResult();
                    unset($tasks[$index]);
                }
            }
        }
    }
}