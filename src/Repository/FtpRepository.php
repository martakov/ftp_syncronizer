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

    public function createDirectory($name)
    {
        return $this->ftpSystem->isDirExist($name) ? false : $this->ftpSystem->ftpMakeDir($name);
    }

    public function uploadFiles($remoteFile, $localFile)
    {
        return $this->ftpSystem->ftpPutFile($remoteFile, $localFile, $mode = FTP_BINARY);
    }

}