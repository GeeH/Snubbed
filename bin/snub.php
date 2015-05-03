<?php

use Snubbed\ControllerSnubber;
use Snubbed\FileWriter;
use Zend\Mvc\Application;

$dir = '/../';
if (strpos(__DIR__, 'vendor')) {
    $dir = '/../../../../';
}
chdir(__DIR__ . $dir);

require_once('vendor/autoload.php');

$configLocation     = isset($argv[1]) ? $argv[1] : 'config/application.config.php';
$abstractController = isset($argv[2]) ? $argv[2] : 'Zend\Mvc\Controller\AbstractActionController';
if (strpos($abstractController, '\\') !== 0) {
    $abstractController = '\\' . $abstractController;
}

// load config
$config = require($configLocation);

// create an application
$application = Application::init($config);

$fileWriter = new FileWriter();

$controllerSnubber = new ControllerSnubber($application, $fileWriter);
$controllerSnubber->generateControllerSnub($abstractController);

$viewSnubber = new \Snubbed\ViewSnubber($application, $fileWriter);
$viewSnubber->generateViewSnubs($application);