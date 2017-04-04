<?php
require 'vendor/autoload.php';
require 'db.php';

use Noodlehaus\Config;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$log = new Logger('main');
$log->pushHandler(new StreamHandler('main.log', Logger::DEBUG));
$log->debug('Reindex started');

$conf = Config::load('config.yml');

$hosts = [
	$conf["host"] . ":" . $conf["port"] // IP + Port
];

$client = Elasticsearch\ClientBuilder::create()
	->setHosts($hosts)
	->build();

/* 0 - waiting for index */
/* 1 - indexed, active */
/* 2 - waiting for removal */
/* 3 - waiting for reindex */
/* 4 - removed, not active */

$sql = "SELECT * FROM plugin_imageviewer_meta JOIN file ON file_id = file.id WHERE statusId = 1 AND ext_index_status = 3";

$source = getSource($sql);
$index = "movies";

foreach($source as $one)
{
  $body = json_decode($one["raw_data"],1);

  $params = [
    'index' => $index,
    'type' => 'image',
    'id' => $one['file_id']
  ];

  $response = $client->delete($params);

  if($response){
    $sql = "UPDATE file SET ext_index_status = 0 WHERE id = " . $one['file_id'];
    $db->query($sql);
  }
}
