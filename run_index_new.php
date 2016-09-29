<?php
require 'vendor/autoload.php';
require 'db.php';

$hosts = [
	'195.26.178.77:9200',          // IP + Port
];

if(!isset($hosts) || count($hosts) < 1) die ("No hosts listed in \$hosts array!\n");

$client = Elasticsearch\ClientBuilder::create()
	->setHosts($hosts)
	->build();

$sql = "SELECT * FROM plugin_imageviewer_meta JOIN file ON file_id = file.id WHERE statusId = 1 AND ext_index_status = 0";

$source = getSource($sql);
$index = "movies";

//Available fields list:
/* "DateTimeOriginal", */
/* "Author", */
/* "Source", */
/* "SourceUrl", */
/* "EventDate", */
/* "Locality", */
/* "Category", */
/* "CreationDate", */
/* "Source", */
/* "Description", */
/* "Title", */
/* "ExifImageLength", */
/* "ExifImageWidth", */
/* "ImageWidth", */
/* "ImageLength" */

$desiredFields = [
"Author",
"Source",
"SourceUrl",
"Title",
"ExifImageLength",
"ExifImageWidth",
];

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
  $fileSource = getSource($sql)->fetch();
  $bodyFiltered["poster"] = "https://photo.mir24.tv/core/cache/plugins/imageviewer/".$fileSource['id']."/".$fileSource['unique_hash']."/280x280_middle.jpg";
  $bodyFiltered["imdbId"] = $fileSource["shortUrl"];
  $bodyFiltered["plot"] = $fileSource["originalFilename"];

  $params = [
    'index' => $index,
    'type' => 'image',
    'body' => $bodyFiltered
  ];
  $response = $client->index($params);

  if($response){
    $sql = "UPDATE file SET indexed = 1 WHERE id = " . $one['file_id'];
    $db->query($sql);
  }
}
