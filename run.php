<?php
require 'vendor/autoload.php';

$hosts = [
	'172.17.0.2:9200',         // IP + Port
];

$client = Elasticsearch\ClientBuilder::create()
	->setHosts($hosts)
	->build();

$params = ['index' => 'movies'];
$response = $client->indices()->delete($params);
print_r($response);
