<?php

namespace Syncronizer\Core;


use Syncronizer\Interfaces\FileSystemInterface;

class FileSystem implements FileSystemInterface
{
    /**
     * @var string
     */
    private $localDirectory;

    public function __construct(string $localDirectory)
    {
        $this->localDirectory = $localDirectory;
    }

    public function scanDir(string $dirPath, $sort = SCANDIR_SORT_NONE)
    {
        return scandir($dirPath, $sort);
    }

    public function isDir(string $name): bool
    {
        return is_dir($name);
    }

    public function fileSize(string $name)
    {
        return filesize($name);
    }

    public function isFileExists($filename): bool
    {
        return file_exists($filename);
    }

    public function getDirectoryName($path): string
    {
        return dirname($path);
    }

    public function fileGetContents($filename)
    {
        return file_get_contents($filename);
    }

    public function filePutContents($filename, $data)
    {
        return file_put_contents($filename, $data);
    }

    public function delete($filename): bool
    {
        return unlink($filename);
    }

    public function isWritable($filename): bool
    {
        return is_writable($filename);
    }

    public function getLocalDirectory(): string
    {
        return $this->localDirectory;
    }
}