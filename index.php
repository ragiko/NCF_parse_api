<?php
require 'vendor/autoload.php';

$app = new \Slim\Slim();

require __DIR__ . '/helper/AppHelper.php';

require __DIR__ . '/src/meet.php';
require __DIR__ . '/src/music.php';

$app->run();
