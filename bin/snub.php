<?php

use Snubbed\ControllerSnubber;
use Zend\Mvc\Application;

$dir = '/../';
if(strpos(__DIR__, 'vendor')) {
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

$controllerSnubber = new ControllerSnubber($application, new \Snubbed\FileWriter());
$controllerSnubber->generateControllerSnub($abstractController);