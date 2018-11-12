<?php

namespace Syncronizer\Core;


use Syncronizer\Exceptions\LoginException;
use Syncronizer\Interfaces\FtpSystemInterface;

class FtpSystem implements FtpSystemInterface
{
    private $connect;
    private $ftpDirectory;

    public function __construct(string $host, string $userName, string $password, string $ftpDirectory)
    {
        $this->connect = $this->ftpConnect($host, $userName, $password);
        $this->setFtpDirectory($ftpDirectory);
    }

    public function ftpDeleteFile(string $remoteFile): bool
    {
        return ftp_delete($this->connect, $this->ftpDirectory . $remoteFile);
    }

    public function ftpPutFile(string $remoteFile, string $localFile, int $mode): bool
    {
        return ftp_put($this->connect, $this->ftpDirectory . $remoteFile, $localFile, $mode);
    }

    public function ftpPutFileAsynchronous(string $remoteFile, string $localFile, int $mode)
    {
        return ftp_nb_put($this->connect, $this->ftpDirectory . $remoteFile, $localFile, $mode);
    }

    public function ftpPutFileAsynchronousContinue()
    {
        return ftp_nb_continue($this->connect);
    }

    public function ftpMakeDir(string $name)
    {
        return ftp_mkdir($this->connect, $this->ftpDirectory . $name);
    }

    public function ftpRemoveDir(string $name): bool
    {
        return ftp_rmdir($this->connect, $this->ftpDirectory . $name);
    }

    public function ftpPassiveMode(bool $pasv): bool
    {
        return ftp_pasv($this->connect, $pasv);
    }

    public function isDirExist(string $name): bool
    {
        return empty(ftp_rawlist($this->connect, $this->ftpDirectory . $name)) ? false : true;
    }

    private function ftpConnect(string $host, string $userName, string $password)
    {
        $connect = ftp_connect($host);

        if (!ftp_login($connect, $userName, $password)) {
            throw new LoginException('ftp login failed');
        }

        return $connect;
    }

    private function setFtpDirectory(string $ftpDirectory): void
    {
        $this->ftpDirectory = $ftpDirectory === '' ? $ftpDirectory : str_replace('/','', $ftpDirectory) . DIRECTORY_SEPARATOR;

    }

}