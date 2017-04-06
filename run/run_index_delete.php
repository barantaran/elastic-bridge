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

/* 0 - waiting for index */
/* 1 - indexed, active */
/* 2 - waiting for removal */
/* 3 - waiting for reindex */
/* 4 - removed, not active */

$sql = "SELECT * FROM plugin_imageviewer_meta JOIN file ON file_id = file.id WHERE statusId = 1 AND ext_index_status = ".$conf["ST_WAIT_FOR_RMV"];

$source = getSource($sql);
$index = "movies";

foreach($source as $one)
{
  $body = json_decode($one["raw_data"],1);

  $params = [
    'index' => $index,
    'type' => 'image',
    'id' => $one['file_id'];
  ];

  $log->debug("Goin to push into index", $params);

  $response = $client->delete($params);

  $log->debug("Elastic response", $response);

  if($response){
    $sql = "UPDATE file SET ext_index_status = ".$conf["ST_REMOVED"]." WHERE id = " . $one['file_id'];
    $dbh->query($sql);
    if($dbh->query($sql))
        $log->debug("Index status updated", $params);
    else
        $log->error("Index status update failed", $params);
  }
}
