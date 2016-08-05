<?php
require 'vendor/autoload.php';
require 'db.php';

$hosts = [
	'172.17.0.4:9200',          // IP + Port
];

if(!isset($hosts) || count($hosts) < 1) die ("No hosts listed in \$hosts array!\n");

$client = Elasticsearch\ClientBuilder::create()
	->setHosts($hosts)
	->build();
/*
$sql = "SELECT * FROM file";
$source = getSource($sql);

foreach($source as $one)
{
	print_r($one);
}
*/

$sql = "SELECT * FROM plugin_imageviewer_meta";

$source = getSource($sql);
$desiredFields = [
"DateTimeOriginal",
"Author",
"EventDate",
"Locality",
"Category",
"CreationDate",
"Source",
"Description",
"Title",
"ImageWidth",
"ImageLength"
];

foreach($source as $one)
{
	$body = json_decode($one["raw_data"],1);

	foreach($desiredFields as $field){
		if(array_key_exists($field,$body)) $bodyFiltered[$field] = $body[$field];
	}

print_r($bodyFiltered);
/*	$params = [
		'index' => 'images',
		'type' => 'image',
		'body' => $body
			];
*/
$params = [
    'index' => 'images',
    'type' => 'image',
    'body' => $bodyFiltered
];
	$response = $client->index($params);
print_r($response);
}

function deleteIndex($index)
{
	$params = ['index' => $index];
	$response = $client->indices()->delete($params);

	return $response;
}
