#! /usr/bin/env php
<?php

declare(strict_types=1);

use Symfony\Component\Yaml\Yaml;
use Syncronizer\Core\Demon;
use Syncronizer\Core\FileSystem;
use Syncronizer\Core\FtpSystem;
use Syncronizer\Core\SystemCalls;
use Syncronizer\Repository\FtpRepository;
use Syncronizer\Services\FileService;

error_reporting(E_ALL);
chdir(dirname(__DIR__));

require_once 'vendor/autoload.php';

define('IN_WORK', false);
define('DONE', false);
$config = Yaml::parseFile('config/config.yml');
$command = getopt('d:')['d'];

$fileSystem = new FileSystem($config['local_directory']);
$systemCalls = new SystemCalls();

$repository = new FtpRepository(new FtpSystem(
    $config['host'],
    $config['user_name'],
    $config['password'],
    $config['ftp_directory']
));

$demon = new Demon($fileSystem, $systemCalls, $config['pid_file']);
$fileService = new FileService($fileSystem, $repository);

try {
    if ($command === "start") {
        $demon->start($fileService);
    }
    if ($command === 'stop') {
        $demon->stop();
    }
} catch (Exception $e) {
    echo $e->getMessage() . PHP_EOL;
}

