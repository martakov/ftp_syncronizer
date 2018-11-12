<?php

namespace Syncronizer\Interfaces;


interface FtpSystemInterface
{
    public function ftpMakeDir(string $name);

    public function ftpRemoveDir(string $name): bool;

    public function ftpPassiveMode(bool $pasv): bool;

    public function isDirExist(string $name): bool;

    public function ftpPutFileAsynchronous(string $remoteFile, string $localFile, $mode);

    public function ftpPutFileAsynchronousContinue();

    public function ftpPutFile($remoteFile, $localFile, $mode): bool;
}
