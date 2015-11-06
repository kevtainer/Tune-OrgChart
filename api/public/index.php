<?php

$autoload_file = "../vendor/autoload.php";
if (!file_exists($autoload_file)) {
    throw new \Exception('fatal: Unable to load dependencies. (pro-tip: `README.md`)');
}

$config_file = "../config.json";
if (!file_exists($config_file)) {
    throw new \Exception('fatal: Unable to load config.json. ¯\_(ツ)_/¯');
}

require_once $autoload_file;

$statsd = new \Tune\StatsD();
$statsd->connect();

\Tune\StatsD::client()->startTiming('execution');

$config = new \Tune\Config();
$config->load($config_file);

$router = new \Tune\Router(new \Slim\Slim());
$router->routes()->run();

\Tune\StatsD::client()->memory('execution');
\Tune\StatsD::client()->endTiming('execution');