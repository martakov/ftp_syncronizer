<?php

namespace Syncronizer\Core;


use Syncronizer\Exceptions\AlreadyRunningException;
use Syncronizer\Exceptions\AlreadyStoppedException;
use Syncronizer\Exceptions\CantStopDaemonException;
use Syncronizer\Exceptions\CouldNotForkException;
use Syncronizer\Exceptions\InvalidPidPathException;
use Syncronizer\Interfaces\FileSystemInterface;
use Syncronizer\Interfaces\SystemCallsInterface;

class Demon
{
    private $fileSystem;
    private $systemCalls;
    private $pidPath;
    private $stopServer = false;
    private $worked = false;

    public function __construct(FileSystemInterface $fileSystem, SystemCallsInterface $systemCalls, $pidPath)
    {
        $this->fileSystem = $fileSystem;
        $this->systemCalls = $systemCalls;
        $this->pidPath = $pidPath;
        $this->systemCalls->listenSignal(SIGTERM, [$this, 'signalHandler']);
    }

    public function start($fileService)
    {
        $runningPid = $this->getPid();

        if ($runningPid) {
            throw new AlreadyRunningException("Daemons already running with pid {$runningPid}");
        }

        $this->checkPidPath();

        $pid = $this->systemCalls->fork();

        if ($pid === -1) {
            throw new CouldNotForkException;
        }

        if ($pid) {
            // родительский процесс
            return true;
        }

        ini_set('error_log','/home/sergei/project/ftp_syncronizer/storage/logs/error.log');
        fclose(STDIN);
        fclose(STDOUT);
        fclose(STDERR);
        $STDIN = fopen('/dev/null', 'r');
        $STDOUT = fopen('/home/sergei/project/ftp_syncronizer/storage/logs/application.log', 'ab');
        $STDERR = fopen('/home/sergei/project/ftp_syncronizer/storage/logs/daemon.log', 'ab');

        $sid = $this->systemCalls->setSid(); // child процесс в лидеры сессии

        if ($sid === -1) {
            $this->systemCalls->quit(ExitConstants::EXIT_CANT_SET_SID);
        }

        $this->fileSystem->filePutContents($this->pidPath, $sid);

        while(!$this->stopServer) {

            $fileService->putFilesOnFtp();
            $this->systemCalls->waitInterval();
            $this->systemCalls->dispatchSignals();
        }
    }

    public function stop(): bool
    {
        $pid = $this->getPid();

        if (!$pid) {
            throw new AlreadyStoppedException('Daemon already stopped');
        }

        if ($this->systemCalls->terminateProcess($pid) === false) {
            throw new CantStopDaemonException("Can't send SIGTERM signal to daemon");
        }

        return true;
    }

    public function signalHandler($signo) {

        switch($signo) {
            case SIGTERM: {
                $this->stopServer = true;
                $this->fileSystem->delete($this->pidPath);
                break;
            }
        }
    }

    public function getPid(): int
    {
        if (!$this->fileSystem->isFileExists($this->pidPath)) {

            return false;
        }
        else {

            if (!$this->systemCalls->isProcessRunning($this->fileSystem->fileGetContents($this->pidPath))) {
                $this->fileSystem->delete($this->pidPath);

                return false;
            }
        }

        return $this->fileSystem->fileGetContents($this->pidPath);
    }

    protected function checkPidPath()
    {
        if ($this->fileSystem->isDir($this->pidPath)) {
            throw new InvalidPidPathException("Pid path \"{$this->pidPath}\" must be file");
        }

        $dir = $this->fileSystem->getDirectoryName($this->pidPath);

        if (empty($dir)) {
            throw new InvalidPidPathException("Invalid directory for pid path \"{$this->pidPath}\"");
        }

        if (!$this->fileSystem->isWritable($dir)) {
            throw new InvalidPidPathException("Directory \"{$this->pidPath}\" is not writable for this user");
        }
    }
}