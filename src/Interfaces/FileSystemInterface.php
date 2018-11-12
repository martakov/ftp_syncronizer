<?php

namespace Syncronizer\Interfaces;


interface FileSystemInterface
{
    public function scanDir(string $dirPath, $sort = null);

    public function isDir(string $name): bool;

    public function fileSize(string $name);

    public function getDirectoryName($path): string;

    public function fileGetContents($filename);

    public function filePutContents($filename, $data);

    public function delete($filename): bool;

    public function isFileExists($filename): bool;

    public function isWritable($filename): bool;

    public function getLocalDirectory(): string;
}