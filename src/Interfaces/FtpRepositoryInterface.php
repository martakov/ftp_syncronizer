<?php

namespace Syncronizer\Interfaces;


interface FtpRepositoryInterface
{
    public function createDirectory(string $name);

    public function uploadFiles(string $remoteFile, string $localFile);

    public function deleteFile(string $remoteFile): bool;
}