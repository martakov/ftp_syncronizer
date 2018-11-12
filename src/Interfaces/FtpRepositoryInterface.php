<?php

namespace Syncronizer\Interfaces;


interface FtpRepositoryInterface
{
    public function createDirectory($name);

    public function uploadFiles(string $remoteFile, string $localFile);
}