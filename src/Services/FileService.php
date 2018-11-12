<?php

namespace Syncronizer\Services;


use Syncronizer\Interfaces\FileSystemInterface;
use Syncronizer\Interfaces\FtpRepositoryInterface;

class FileService
{
    /**
     * @var FileSystemInterface
     */
    private $fileSystem;
    private $repository;
    private $files = [];
    private $dirTreeCreated = false;

    public function __construct(FileSystemInterface $fileSystem, FtpRepositoryInterface $repository)
    {
        $this->fileSystem = $fileSystem;
        $this->repository = $repository;
    }

    public function createDirectoryTree($relativeDirectory = null)
    {
        $absoluteDirectory = $this->buildPathToLocalDirectory($relativeDirectory);
        $files = $this->getFilesFromLocalDirectory($relativeDirectory);

        foreach ($files as $i => $file) {

            $relativePath = $this->buildRelativePath($relativeDirectory, $file);
            $absolutePath = $this->buildAbsolutePath($absoluteDirectory, $file);

            if ($this->fileSystem->isDir($absolutePath)) {
                $this->repository->createDirectory($relativePath);
                $this->createDirectoryTree($relativePath);
            }
        }

        return true;
    }

    public function putFilesOnFtp($relativeDirectory = null)
    {
        if (!$this->dirTreeCreated) {
            $this->createDirectoryTree();
            $this->dirTreeCreated = true;
        }

        $absoluteDirectory = $this->buildPathToLocalDirectory($relativeDirectory);
        $files = $this->getFilesFromLocalDirectory($relativeDirectory);

        foreach ($files as $i => $file) {

            $relativePath = $this->buildRelativePath($relativeDirectory, $file);
            $absolutePath = $this->buildAbsolutePath($absoluteDirectory, $file);

            if ($this->fileSystem->isDir($absolutePath)) {
                $this->putFilesOnFtp($relativePath);
            }
            else {
                $hash = $this->getFileHash($absolutePath);

                if (isset($this->files[$absolutePath]) && $this->files[$absolutePath] === $hash) {
                    // файл загружен, хэш совпадает
                    continue;
                }
                elseif (isset($this->files[$absolutePath]) && $this->files[$absolutePath] !== $hash) {
                    // файл загружен, хэш не совпадает
                    $this->repository->uploadFiles($relativePath, $absolutePath);
                    $this->files[$absolutePath] = $hash;
                }
                else {
                    // файл не загружен
                    $this->repository->uploadFiles($relativePath, $absolutePath);
                    $this->files[$absolutePath] = $hash;
                }
            }
        }

        return true;
    }

    private function buildPathToLocalDirectory($relativeDirectory)
    {
        $localDirectory = $this->fileSystem->getLocalDirectory();
        return $relativeDirectory === null ? $localDirectory : $localDirectory . DIRECTORY_SEPARATOR . $relativeDirectory;
    }

    private function getFilesFromLocalDirectory($relativeDirectory)
    {
        return array_diff($this->fileSystem->scanDir($this->buildPathToLocalDirectory($relativeDirectory)), ['.', '..']);
    }

    private function buildRelativePath($relativeDirectory, $file)
    {
        return $relativeDirectory === null ? $file : $relativeDirectory . DIRECTORY_SEPARATOR . $file;
    }

    private function buildAbsolutePath($absoluteDirectory, $file)
    {
        return $absoluteDirectory . DIRECTORY_SEPARATOR . $file;
    }

    private function getFileHash($absolutePath)
    {
        $size = $this->fileSystem->fileSize($absolutePath);
        $hash = md5($absolutePath . $size);

        return $hash;
    }

}