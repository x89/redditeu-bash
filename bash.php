<?php

$credentials = require __DIR__.'/credentials.php';

foreach (glob(__DIR__.'/includes/*.php') as $file) {
	require_once $file;
}

$config = [
	'rootDir'       => __DIR__,
	'password'      => $credentials['password'],
	'title'         => 'SCReddit QDB',
	'enableCaptcha' => $credentials['enableCaptcha'],
	'captchaKey'	=> $credentials['captchaKey'],
	'latest'        => 10,
	'top'           => 25,
	'browsePP'      => 50,
	'random'        => 25,
	'search'        => 25,
];

$app = new Application($config);

$app->connectMysql($credentials['mysqlUser'], $credentials['mysqlPass'], 'bash');

$app->run();
