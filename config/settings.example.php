<?php

$settings = [];

// Slim settings
$settings['displayErrorDetails'] = true;
$settings['determineRouteBeforeAppMiddleware'] = true;

// Path settings
$settings['root'] = dirname(__DIR__);
$settings['datagator'] = $settings['root'] . '/includes';
$settings['temp'] = $settings['root'] . '/tmp';
$settings['public'] = $settings['root'] . '/html';

// View settings
$settings['twig'] = [
  'path' => $settings['datagator'] . '/admin/templates',
  'cache_enabled' => false,
  'cache_path' =>  $settings['temp'] . '/twig_cache'
];

// Database settings
$settings['db'] = [
  'base' => $settings['datagator'] . '/db/dbBase.yaml',
  'driver' => 'mysqli',
  'host' => 'localhost',
  'username' => 'root',
  'password' => '',
  'database' => 'test',
  'options' => [],
  'charset' => 'utf8',
  'collation' => 'utf8_unicode_ci'
];

// User settings
$settings['user']['token_life'] = '+1 hour';

return $settings;