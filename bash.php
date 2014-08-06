<?php

foreach (glob(__DIR__.'/includes/*.php') as $file) {
	require_once $file;
}

$config = [
	'rootDir'       => __DIR__,
	'title'         => 'SCReddit QDB',
	'enableCaptcha' => false,
	'latest'        => 10,
	'top'           => 25,
	'browsePP'      => 50,
	'random'        => 25,
	'search'        => 25,
];

$app = new Application($config);

$app->connectMysql('bash', 'bash', 'bash');

$app->run();
