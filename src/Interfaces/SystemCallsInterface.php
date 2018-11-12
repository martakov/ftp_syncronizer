<?php

namespace Syncronizer\Interfaces;


interface SystemCallsInterface
{
    public function fork(): int;

    public function dispatchSignals(): bool;

    public function setProcessTitle(string $title): bool;

    public function listenSignal(int $sig, $handler): bool;

    public function getExitCode($status): int;

    public function setSid(): int;

    public function quit(int $status): void;

    public function terminateProcess(int $pid): bool;

    public function isProcessRunning(int $pid): bool;

    public function synchronousWaiting(int $pid, &$status): int;

    public function aSynchronousWaiting(int $pid, &$status): int;

    public function waitInterval(): void;

    public function getPid();
}