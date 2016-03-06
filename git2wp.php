<?php
require_once 'vendor/autoload.php';
use Git2WP\SyncCommand;
use AW2MW\ExportAddressCommand;
use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new SyncCommand());
$application->run();
