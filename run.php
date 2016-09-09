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

$sql = "SELECT * FROM file";
$source = getSource($sql);
/*
foreach($source as $one)
{
	print_r($one);
}
*/

$sql = "SELECT * FROM plugin_imageviewer_meta JOIN file ON file_id = file.id WHERE statusId = 1";

$source = getSource($sql);
$index = "movies";
$desiredFields = [
//"DateTimeOriginal",
"Author",
//"EventDate",
//"Locality",
//"Category",
//"CreationDate",
//"Source",
//"Description",
"Title",
"ExifImageLength",
"ExifImageWidth",
//"ImageWidth",
//"ImageLength"
];
//echo "\n__SOURCE__\n";
//print_r(get_class_methods($source));
//die;
foreach($source as $one)
{
echo "\n__ONE__\n";
print_r($one);
	$body = json_decode($one["raw_data"],1);

	foreach($desiredFields as $field){
		if(array_key_exists($field,$body)) $bodyFiltered[mb_strtolower($field)] = $body[$field];
	}
	if(!isset($bodyFiltered)) continue;
	
	$sql = "SELECT * FROM file WHERE id =".$one['file_id'];
echo "\n__SQL__\n";
print_r($sql);
	$fileSource = getSource($sql)->fetch();
echo "\n__FILESOURCE__\n";
print_r($fileSource);
	$bodyFiltered["poster"] = "http://195.26.178.77/core/cache/plugins/imageviewer/".$fileSource['id']."/".$fileSource['unique_hash']."/280x280_middle.jpg";
echo "\n__BODYFILTERED__\n";
	$bodyFiltered["imdbId"] = $fileSource["shortUrl"];
	$bodyFiltered["plot"] = $fileSource["originalFilename"];
	print_r($bodyFiltered);

	$params = [
		'index' => $index,
		'type' => 'image',
		'body' => $bodyFiltered
			];
	$response = $client->index($params);
echo "\n__RESPONSE__\n";
	print_r($response);
}
