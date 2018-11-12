<?php

namespace Syncronizer\Services;


use Syncronizer\Interfaces\FileServiceInterface;
use Syncronizer\Interfaces\FileSystemInterface;
use Syncronizer\Interfaces\FtpRepositoryInterface;

class FileService implements FileServiceInterface
{
    /**
     * @var FileSystemInterface
     */
    private $fileSystem;
    private $repository;
    private $files = [];
    private $filesBuffer = [];
    private $dirTreeCreated = false;

    public function __construct(FileSystemInterface $fileSystem, FtpRepositoryInterface $repository)
    {
        $this->fileSystem = $fileSystem;
        $this->repository = $repository;
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
                $this->filesBuffer[$relativePath] = $hash;

                if (isset($this->files[$relativePath]) && $this->files[$relativePath] === $hash) {
                    // файл загружен, хеш совпадает
                    continue;
                }
                elseif (isset($this->files[$relativePath]) && $this->files[$relativePath] !== $hash) {
                    // файл загружен, хеш не совпадает
                    $this->repository->uploadFiles($relativePath, $absolutePath);
                    $this->files[$relativePath] = $hash;
                }
                else {
                    // файл не загружен
                    $this->repository->uploadFiles($relativePath, $absolutePath);
                    $this->files[$relativePath] = $hash;
                }
            }
        }

        return true;
    }

    public function compareFilesMap()
    {
        if (count($this->filesBuffer) !== count($this->files)) {
            $oldFiles = array_diff_key($this->files, $this->filesBuffer);

            foreach ($oldFiles as $key => $file) {
                $this->repository->deleteFile($key);
                unset($this->files[$key]);
            }
        }

        $this->filesBuffer = [];
    }

    private function createDirectoryTree($relativeDirectory = null)
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