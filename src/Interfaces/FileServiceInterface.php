<?php

namespace Syncronizer\Interfaces;


interface FileServiceInterface
{
    public function putFilesOnFtp($relativeDirectory = '');

    public function compareFilesMap();
}