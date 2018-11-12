<?php

namespace Syncronizer\Interfaces;


interface FtpSystemInterface
{
    public function ftpMakeDir(string $name);

    public function ftpRemoveDir(string $name): bool;

    public function ftpPassiveMode(bool $pasv): bool;

    public function isDirExist(string $name): bool;

    public function ftpPutFileAsynchronous(string $remoteFile, string $localFile, int $mode);

    public function ftpPutFileAsynchronousContinue();

    public function ftpPutFile(string $remoteFile, string $localFile, int $mode): bool;

    public function ftpDeleteFile(string $file): bool;
}
