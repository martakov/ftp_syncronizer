<?php

namespace Syncronizer\Repository;


use Syncronizer\Interfaces\FtpRepositoryInterface;
use Syncronizer\Interfaces\FtpSystemInterface;

class FtpRepository implements FtpRepositoryInterface
{
    /**
     * @var FtpSystemInterface
     */
    private $ftpSystem;

    public function __construct(FtpSystemInterface $ftpSystem)
    {
        $this->ftpSystem = $ftpSystem;
    }

    public function createDirectory(string $name)
    {
        return $this->ftpSystem->isDirExist($name) ? false : $this->ftpSystem->ftpMakeDir($name);
    }

    public function uploadFiles(string $remoteFile, string $localFile)
    {
        return $this->ftpSystem->ftpPutFile($remoteFile, $localFile, $mode = FTP_BINARY);
    }

    public function deleteFile(string $remoteFile): bool
    {
        return $this->ftpSystem->ftpDeleteFile($remoteFile);
    }

}