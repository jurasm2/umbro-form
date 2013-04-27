<?php

/**
 * My Application bootstrap file.
 */
use Nette\Application\Routers\Route;


// Load Nette Framework
require LIBS_DIR . '/Nette/loader.php';


// Configure application
$configurator = new Nette\Config\Configurator;
$configurator->setDebugMode('194.228.150.162');

// Enable Nette Debugger for error visualisation & logging
//$configurator->setDebugMode($configurator::AUTO);
$configurator->enableDebugger(__DIR__ . '/../log');

// Enable RobotLoader - this will load all classes automatically
$configurator->setTempDirectory(__DIR__ . '/../temp');

$configurator->createRobotLoader()
	->addDirectory(APP_DIR)
	->addDirectory(LIBS_DIR)
	->register();

// Create Dependency Injection container from config.neon file
$configurator->addConfig(__DIR__ . '/config/config.neon');
$container = $configurator->createContainer();

// Setup router
$container->router[] = new Route('test', 'Cron:test');
$container->router[] = new Route('cron', 'Cron:default');
$container->router[] = new Route('send-sunday-info', 'Cron:sundayInfo');
$container->router[] = new Route('send-info', 'Cron:info');
$container->router[] = new Route('sent/<eventId>', 'Default:sent');
//$container->router[] = new Route('index.php', 'Homepage:default', Route::ONE_WAY);
$container->router[] = new Route('[<hash>]', 'Default:default');


//$container->application->errorPresenter = 'Error';
//$container->application->catchExceptions = TRUE;

// Configure and run the application!
$container->application->run();
