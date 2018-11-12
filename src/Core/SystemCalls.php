<?php

namespace Syncronizer\Core;


use Syncronizer\Interfaces\SystemCallsInterface;

class SystemCalls implements SystemCallsInterface
{
    public function fork(): int
    {
        return pcntl_fork();
    }

    public function dispatchSignals(): bool
    {
        return pcntl_signal_dispatch();
    }

    public function setProcessTitle(string $title): bool
    {
        return cli_set_process_title($title);
    }

    public function listenSignal(int $sig, $handler): bool
    {
        return pcntl_signal($sig, $handler);
    }

    public function getExitCode($status): int
    {
        return pcntl_wexitstatus($status);
    }

    public function setSid(): int
    {
        return posix_setsid();
    }

    public function quit(int $status): void
    {
        exit($status);
    }

    public function terminateProcess(int $pid): bool
    {
        return posix_kill($pid, SIGTERM);
    }

    public function isProcessRunning(int $pid): bool
    {
        return posix_kill($pid, SIG_DFL);
    }

    public function synchronousWaiting(int $pid, &$status): int
    {
        return pcntl_waitpid($pid, $status);
    }

    public function aSynchronousWaiting(int $pid, &$status): int
    {
        return pcntl_waitpid($pid, $status, WNOHANG);
    }

    public function waitInterval(): void
    {
        usleep(1000000);
    }

    public function getPid()
    {
        return getmypid();
    }
}